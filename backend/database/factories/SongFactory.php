<?php

namespace Database\Factories;

use App\Models\Song;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Song>
 */
class SongFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Song::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->words(3, true) . ' - ' . $this->faker->name(),
            'youtube_url' => $this->generateYoutubeUrl(),
            'position' => null,
            'plays_count' => $this->faker->numberBetween(0, 10000),
        ];
    }

    /**
     * Indicate that the song is in the top 5.
     */
    public function topFive(): static
    {
        return $this->state(fn (array $attributes) => [
            'position' => $this->faker->unique()->numberBetween(1, 5),
        ]);
    }

    /**
     * Indicate that the song is in position 1.
     */
    public function firstPlace(): static
    {
        return $this->state(fn (array $attributes) => [
            'position' => 1,
        ]);
    }

    /**
     * Indicate that the song is in position 2.
     */
    public function secondPlace(): static
    {
        return $this->state(fn (array $attributes) => [
            'position' => 2,
        ]);
    }

    /**
     * Indicate that the song has high play count.
     */
    public function popular(): static
    {
        return $this->state(fn (array $attributes) => [
            'plays_count' => $this->faker->numberBetween(5000, 50000),
        ]);
    }

    /**
     * Indicate that the song has no plays.
     */
    public function unplayed(): static
    {
        return $this->state(fn (array $attributes) => [
            'plays_count' => 0,
        ]);
    }

    /**
     * Generate a valid YouTube URL.
     */
    private function generateYoutubeUrl(): string
    {
        $videoId = $this->faker->regexify('[a-zA-Z0-9_-]{11}');
        $formats = [
            'https://www.youtube.com/watch?v=' . $videoId,
            'https://youtu.be/' . $videoId,
            'https://youtube.com/watch?v=' . $videoId,
        ];

        return $this->faker->randomElement($formats);
    }
}