<?php

namespace Database\Factories;

use App\Enum\LastVideoPeriodEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\YoutubeChannel>
 */
class YoutubeChannelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = ['Gaming', 'Music', 'Education', 'Entertainment', 'Technology', 'Sports', 'News', 'Comedy'];
        $languages = ['Chinese', 'English', 'French', 'German', 'Hindi', 'Portuguese', 'Russian', 'Spanish'];
        $regions = ['Australia', 'Brazil', 'Canada', 'China', 'France','Germany', 'India',  'Russia', 'UK', 'USA'];

        return [
            'title' => $this->faker->company() . ' Channel',
            'subscribers_count' => $this->faker->numberBetween(100, 10000000),
            'category' => $this->faker->randomElement($categories),
            'language' => $this->faker->randomElement($languages),
            'region' => $this->faker->randomElement($regions),
            'last_video_published_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'engagement_rate' => $this->faker->randomFloat(2, 0.1, 15.0),
            'average_views' => $this->faker->numberBetween(1000, 1000000),
            'created_at' => $this->faker->dateTimeBetween('-2 years', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }

    /**
     * Indicate that the channel is in the Gaming category.
     */
    public function gaming(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'Gaming',
        ]);
    }

    /**
     * Indicate that the channel is in the Music category.
     */
    public function music(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'Music',
        ]);
    }

    /**
     * Indicate that the channel is in the Education category.
     */
    public function education(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'Education',
        ]);
    }

    /**
     * Indicate that the channel is in English.
     */
    public function english(): static
    {
        return $this->state(fn (array $attributes) => [
            'language' => 'English',
        ]);
    }

    /**
     * Indicate that the channel is in Russian.
     */
    public function russian(): static
    {
        return $this->state(fn (array $attributes) => [
            'language' => 'Russian',
        ]);
    }

    /**
     * Indicate that the channel is from the US.
     */
    public function us(): static
    {
        return $this->state(fn (array $attributes) => [
            'region' => 'US',
        ]);
    }

    /**
     * Indicate that the channel is from Russia.
     */
    public function russia(): static
    {
        return $this->state(fn (array $attributes) => [
            'region' => 'Russia',
        ]);
    }

    /**
     * Indicate that the channel has high engagement rate.
     */
    public function highEngagement(): static
    {
        return $this->state(fn (array $attributes) => [
            'engagement_rate' => $this->faker->randomFloat(2, 8.0, 15.0),
        ]);
    }

    /**
     * Indicate that the channel has low engagement rate.
     */
    public function lowEngagement(): static
    {
        return $this->state(fn (array $attributes) => [
            'engagement_rate' => $this->faker->randomFloat(2, 0.1, 3.0),
        ]);
    }

    /**
     * Indicate that the channel has many subscribers.
     */
    public function popular(): static
    {
        return $this->state(fn (array $attributes) => [
            'subscribers_count' => $this->faker->numberBetween(100000, 10000000),
        ]);
    }

    /**
     * Indicate that the channel has few subscribers.
     */
    public function small(): static
    {
        return $this->state(fn (array $attributes) => [
            'subscribers_count' => $this->faker->numberBetween(100, 10000),
        ]);
    }

    /**
     * Indicate that the channel published a video recently.
     */
    public function recentlyActive(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_video_published_at' => $this->faker->dateTimeBetween('-7 days', 'now'),
        ]);
    }

    /**
     * Indicate that the channel published a video in the last month.
     */
    public function activeLastMonth(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_video_published_at' => $this->faker->dateTimeBetween('-30 days', '-7 days'),
        ]);
    }

    /**
     * Indicate that the channel published a video in the last year.
     */
    public function activeLastYear(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_video_published_at' => $this->faker->dateTimeBetween('-1 year', '-30 days'),
        ]);
    }

    /**
     * Indicate that the channel is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_video_published_at' => $this->faker->dateTimeBetween('-2 years', '-1 year'),
        ]);
    }
}
