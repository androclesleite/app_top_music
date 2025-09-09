<?php

namespace Database\Factories;

use App\Models\SongSuggestion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SongSuggestion>
 */
class SongSuggestionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SongSuggestion::class;

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
            'status' => SongSuggestion::STATUS_PENDING,
            'suggested_by' => $this->faker->name(),
            'reviewed_by' => null,
            'reviewed_at' => null,
        ];
    }

    /**
     * Indicate that the suggestion is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SongSuggestion::STATUS_PENDING,
            'reviewed_by' => null,
            'reviewed_at' => null,
        ]);
    }

    /**
     * Indicate that the suggestion is approved.
     */
    public function approved(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => SongSuggestion::STATUS_APPROVED,
                'reviewed_by' => User::factory(),
                'reviewed_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            ];
        });
    }

    /**
     * Indicate that the suggestion is rejected.
     */
    public function rejected(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => SongSuggestion::STATUS_REJECTED,
                'reviewed_by' => User::factory(),
                'reviewed_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            ];
        });
    }

    /**
     * Set a specific reviewer.
     */
    public function reviewedBy(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'reviewed_by' => $user->id,
            'reviewed_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
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