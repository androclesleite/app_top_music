<?php

namespace Database\Seeders;

use App\Models\SongSuggestion;
use App\Models\User;
use Illuminate\Database\Seeder;

class SuggestionSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@techpines.com.br')->first();
        
        $suggestions = [
            [
                'title' => 'Chalana',
                'youtube_url' => 'https://www.youtube.com/watch?v=zchalana123',
                'status' => 'pending',
                'suggested_by' => 'João da Silva',
            ],
            [
                'title' => 'Cuitelinho',
                'youtube_url' => 'https://www.youtube.com/watch?v=cuitel456',
                'status' => 'approved',
                'suggested_by' => 'Maria Santos',
                'reviewed_by' => $admin?->id,
                'reviewed_at' => now()->subDays(2),
            ],
            [
                'title' => 'Peão Laçador',
                'youtube_url' => 'https://www.youtube.com/watch?v=peaolac789',
                'status' => 'rejected',
                'suggested_by' => 'José Costa',
                'reviewed_by' => $admin?->id,
                'reviewed_at' => now()->subDays(1),
            ],
            [
                'title' => 'Marvada Pinga',
                'youtube_url' => 'https://www.youtube.com/watch?v=marvada012',
                'status' => 'pending',
                'suggested_by' => 'Ana Oliveira',
            ],
            [
                'title' => 'Beijinho Doce',
                'youtube_url' => 'https://www.youtube.com/watch?v=beijinho345',
                'status' => 'pending',
                'suggested_by' => 'Pedro Lima',
            ],
        ];

        foreach ($suggestions as $suggestion) {
            SongSuggestion::create($suggestion);
        }
    }
}