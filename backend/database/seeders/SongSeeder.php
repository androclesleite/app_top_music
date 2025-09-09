<?php

namespace Database\Seeders;

use App\Models\Song;
use Illuminate\Database\Seeder;

class SongSeeder extends Seeder
{
    public function run(): void
    {
        $topSongs = [
            [
                'title' => 'Pagode em Brasília',
                'youtube_url' => 'https://www.youtube.com/watch?v=qxVZQrNr5Hs',
                'position' => 1,
                'plays_count' => 1500000,
            ],
            [
                'title' => 'Rei do Gado',
                'youtube_url' => 'https://www.youtube.com/watch?v=5hxmzWEyHJQ',
                'position' => 2,
                'plays_count' => 1350000,
            ],
            [
                'title' => 'Boi Soberano',
                'youtube_url' => 'https://www.youtube.com/watch?v=VGMYLwQyXuE',
                'position' => 3,
                'plays_count' => 1200000,
            ],
            [
                'title' => 'Festa do Peão',
                'youtube_url' => 'https://www.youtube.com/watch?v=UqDwgYt_IhI',
                'position' => 4,
                'plays_count' => 1100000,
            ],
            [
                'title' => 'Cabocla Teresa',
                'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'position' => 5,
                'plays_count' => 1000000,
            ],
        ];

        $otherSongs = [
            [
                'title' => 'Moda da Pinga',
                'youtube_url' => 'https://www.youtube.com/watch?v=abc123defgh',
                'position' => null,
                'plays_count' => 950000,
            ],
            [
                'title' => 'Tristeza do Jeca',
                'youtube_url' => 'https://www.youtube.com/watch?v=def456ghijk',
                'position' => null,
                'plays_count' => 900000,
            ],
            [
                'title' => 'Viola Chorando',
                'youtube_url' => 'https://www.youtube.com/watch?v=ghi789jklmn',
                'position' => null,
                'plays_count' => 850000,
            ],
            [
                'title' => 'Saudade da Minha Terra',
                'youtube_url' => 'https://www.youtube.com/watch?v=jkl012mnopq',
                'position' => null,
                'plays_count' => 800000,
            ],
            [
                'title' => 'Carreiro Véio',
                'youtube_url' => 'https://www.youtube.com/watch?v=mno345pqrst',
                'position' => null,
                'plays_count' => 750000,
            ],
            [
                'title' => 'Boiadeiro Errante',
                'youtube_url' => 'https://www.youtube.com/watch?v=pqr678stuvw',
                'position' => null,
                'plays_count' => 700000,
            ],
            [
                'title' => 'Chico Mineiro',
                'youtube_url' => 'https://www.youtube.com/watch?v=stu901vwxyz',
                'position' => null,
                'plays_count' => 650000,
            ],
            [
                'title' => 'Menino da Porteira',
                'youtube_url' => 'https://www.youtube.com/watch?v=vwx234yzabc',
                'position' => null,
                'plays_count' => 600000,
            ],
        ];

        // Inserir top 5 músicas
        foreach ($topSongs as $song) {
            Song::create($song);
        }

        // Inserir outras músicas
        foreach ($otherSongs as $song) {
            Song::create($song);
        }
    }
}