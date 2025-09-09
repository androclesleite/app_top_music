<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReviewSuggestionRequest;
use App\Http\Requests\StoreSuggestionRequest;
use App\Http\Resources\SongResource;
use App\Http\Resources\SuggestionResource;
use App\Services\SuggestionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SuggestionController extends Controller
{
    public function __construct(
        protected SuggestionService $suggestionService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'status' => ['sometimes', 'string', 'in:pending,approved,rejected'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'search' => ['sometimes', 'string', 'max:255']
        ]);

        try {
            if ($request->filled('search')) {
                $suggestions = $this->suggestionService->searchSuggestions(
                    $request->get('search'),
                    $request->get('per_page', 15)
                );
            } else {
                $suggestions = $this->suggestionService->getAllSuggestions(
                    $request->get('per_page', 15),
                    $request->get('status')
                );
            }

            return response()->json([
                'success' => true,
                'data' => SuggestionResource::collection($suggestions->items()),
                'meta' => [
                    'current_page' => $suggestions->currentPage(),
                    'from' => $suggestions->firstItem(),
                    'last_page' => $suggestions->lastPage(),
                    'per_page' => $suggestions->perPage(),
                    'to' => $suggestions->lastItem(),
                    'total' => $suggestions->total(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar sugestões: ' . $e->getMessage()
            ], 500);
        }
    }

    public function pending(Request $request): JsonResponse
    {
        $request->validate([
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100']
        ]);

        $suggestions = $this->suggestionService->getPendingSuggestions(
            $request->get('per_page', 15)
        );

        return response()->json([
            'success' => true,
            'data' => SuggestionResource::collection($suggestions->items()),
            'meta' => [
                'current_page' => $suggestions->currentPage(),
                'from' => $suggestions->firstItem(),
                'last_page' => $suggestions->lastPage(),
                'per_page' => $suggestions->perPage(),
                'to' => $suggestions->lastItem(),
                'total' => $suggestions->total(),
            ]
        ]);
    }

    public function store(StoreSuggestionRequest $request): JsonResponse
    {
        try {
            $suggestion = $this->suggestionService->createSuggestion($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Sugestão enviada com sucesso!',
                'data' => new SuggestionResource($suggestion)
            ], 201);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao enviar a sugestão: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        $suggestion = $this->suggestionService->getSuggestion($id);

        if (!$suggestion) {
            return response()->json([
                'success' => false,
                'message' => 'Sugestão não encontrada.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new SuggestionResource($suggestion->load('reviewedBy'))
        ]);
    }

    public function update(ReviewSuggestionRequest $request, int $id): JsonResponse
    {
        $suggestion = $this->suggestionService->getSuggestion($id);

        if (!$suggestion) {
            return response()->json([
                'success' => false,
                'message' => 'Sugestão não encontrada.'
            ], 404);
        }

        try {
            $action = $request->validated('status');
            $reviewerId = $request->user()->id;

            if ($action === 'approve') {
                $song = $this->suggestionService->approveSuggestion($suggestion, $reviewerId);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Sugestão aprovada com sucesso! A música foi adicionada ao catálogo.',
                    'data' => [
                        'suggestion' => new SuggestionResource($suggestion->fresh()->load('reviewedBy')),
                        'song' => new SongResource($song)
                    ]
                ]);
                
            } else {
                $this->suggestionService->rejectSuggestion($suggestion, $reviewerId);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Sugestão rejeitada.',
                    'data' => new SuggestionResource($suggestion->fresh()->load('reviewedBy'))
                ]);
            }

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao processar a sugestão: ' . $e->getMessage()
            ], 500);
        }
    }

    public function stats(): JsonResponse
    {
        $stats = $this->suggestionService->getSuggestionStats();

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}