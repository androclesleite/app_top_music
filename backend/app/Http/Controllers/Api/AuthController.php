<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->authenticate(
                $request->validated('email'),
                $request->validated('password')
            );

            return response()->json([
                'success' => true,
                'message' => 'Login realizado com sucesso.',
                'data' => [
                    'user' => new UserResource($result['user']),
                    'token' => $result['token'],
                ]
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Credenciais inválidas.',
                'errors' => $e->errors()
            ], 422);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return response()->json([
            'success' => true,
            'message' => 'Logout realizado com sucesso.'
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => new UserResource($request->user())
        ]);
    }

    public function refresh(Request $request): JsonResponse
    {
        $newToken = $this->authService->refreshToken($request->user());

        return response()->json([
            'success' => true,
            'message' => 'Token renovado com sucesso.',
            'data' => [
                'token' => $newToken,
                'user' => new UserResource($request->user())
            ]
        ]);
    }
}