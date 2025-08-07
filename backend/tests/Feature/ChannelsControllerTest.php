<?php

namespace Tests\Feature;

use App\DTO\RecommendationsDTO;
use App\Models\YoutubeChannel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ChannelsControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    
    public function test_returns_recommendations_without_filters()
    {
        // Создаем тестовые каналы
        YoutubeChannel::factory()->count(3)->create();

        $response = $this->getJson('/api/recommendations');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'subscribers_count',
                        'category',
                        'language',
                        'region',
                        'last_video_published_at',
                        'engagement_rate',
                        'average_views'
                    ]
                ]
            ]);
    }

    
    public function test_filters_by_category()
    {
        YoutubeChannel::factory()->create(['category' => 'Gaming']);
        YoutubeChannel::factory()->create(['category' => 'Music']);
        YoutubeChannel::factory()->create(['category' => 'Education']);

        $response = $this->getJson('/api/recommendations?category=Gaming');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('Gaming', $response->json('data.0.category'));
    }

    
    public function test_filters_by_subscriber_count_range()
    {
        YoutubeChannel::factory()->create(['subscribers_count' => 1000]);
        YoutubeChannel::factory()->create(['subscribers_count' => 5000]);
        YoutubeChannel::factory()->create(['subscribers_count' => 10000]);

        $response = $this->getJson('/api/recommendations?min_subscribers=2000&max_subscribers=8000');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals(5000, $response->json('data.0.subscribers_count'));
    }

    
    public function test_filters_by_language()
    {
        YoutubeChannel::factory()->create(['language' => 'English']);
        YoutubeChannel::factory()->create(['language' => 'Russian']);
        YoutubeChannel::factory()->create(['language' => 'Spanish']);

        $response = $this->getJson('/api/recommendations?language=russian');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('Russian', $response->json('data.0.language'));
    }

    
    public function test_filters_by_region()
    {
        YoutubeChannel::factory()->create(['region' => 'USA']);
        YoutubeChannel::factory()->create(['region' => 'Russia']);
        YoutubeChannel::factory()->create(['region' => 'UK']);

        $response = $this->getJson('/api/recommendations?region=USA');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('USA', $response->json('data.0.region'));
    }

    
    public function test_filters_by_last_video_period_last_7_days()
    {
        $recentChannel = YoutubeChannel::factory()->create([
            'last_video_published_at' => now()->subDays(3)
        ]);
        $oldChannel = YoutubeChannel::factory()->create([
            'last_video_published_at' => now()->subDays(10)
        ]);

        $response = $this->getJson('/api/recommendations?last_video_period=last_7_days');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals($recentChannel->id, $response->json('data.0.id'));
    }

    
    public function test_filters_by_last_video_period_last_month()
    {
        $recentChannel = YoutubeChannel::factory()->create([
            'last_video_published_at' => now()->subDays(15)
        ]);
        $oldChannel = YoutubeChannel::factory()->create([
            'last_video_published_at' => now()->subMonths(2)
        ]);

        $response = $this->getJson('/api/recommendations?last_video_period=last_month');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals($recentChannel->id, $response->json('data.0.id'));
    }

    
    public function test_filters_by_last_video_period_last_year()
    {
        $recentChannel = YoutubeChannel::factory()->create([
            'last_video_published_at' => now()->subMonths(6)
        ]);
        $oldChannel = YoutubeChannel::factory()->create([
            'last_video_published_at' => now()->subYears(2)
        ]);

        $response = $this->getJson('/api/recommendations?last_video_period=last_year');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals($recentChannel->id, $response->json('data.0.id'));
    }

    
    public function test_sorts_by_engagement_rate_asc()
    {
        YoutubeChannel::factory()->create(['engagement_rate' => 5.0]);
        YoutubeChannel::factory()->create(['engagement_rate' => 2.0]);
        YoutubeChannel::factory()->create(['engagement_rate' => 8.0]);

        $response = $this->getJson('/api/recommendations?sort_key=engagement_rate&sort_direction=asc');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals(2.0, $data[0]['engagement_rate']);
        $this->assertEquals(5.0, $data[1]['engagement_rate']);
        $this->assertEquals(8.0, $data[2]['engagement_rate']);
    }

    
    public function test_sorts_by_engagement_rate_desc()
    {
        YoutubeChannel::factory()->create(['engagement_rate' => 5.0]);
        YoutubeChannel::factory()->create(['engagement_rate' => 2.0]);
        YoutubeChannel::factory()->create(['engagement_rate' => 8.0]);

        $response = $this->getJson('/api/recommendations?sort_key=engagement_rate&sort_direction=desc');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals(8.0, $data[0]['engagement_rate']);
        $this->assertEquals(5.0, $data[1]['engagement_rate']);
        $this->assertEquals(2.0, $data[2]['engagement_rate']);
    }

    
    public function test_sorts_by_average_views_asc()
    {
        YoutubeChannel::factory()->create(['average_views' => 10000]);
        YoutubeChannel::factory()->create(['average_views' => 5000]);
        YoutubeChannel::factory()->create(['average_views' => 15000]);

        $response = $this->getJson('/api/recommendations?sort_key=average_views&sort_direction=asc');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals(5000, $data[0]['average_views']);
        $this->assertEquals(10000, $data[1]['average_views']);
        $this->assertEquals(15000, $data[2]['average_views']);
    }

    
    public function test_combines_multiple_filters()
    {
        YoutubeChannel::factory()->create([
            'category' => 'Gaming',
            'language' => 'English',
            'subscribers_count' => 5000,
            'engagement_rate' => 3.0
        ]);
        YoutubeChannel::factory()->create([
            'category' => 'Gaming',
            'language' => 'Russian',
            'subscribers_count' => 5000,
            'engagement_rate' => 5.0
        ]);
        YoutubeChannel::factory()->create([
            'category' => 'Music',
            'language' => 'English',
            'subscribers_count' => 5000,
            'engagement_rate' => 4.0
        ]);

        $response = $this->getJson('/api/recommendations?category=Gaming&language=english&min_subscribers=3000&sort_key=engagement_rate&sort_direction=desc');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('Gaming', $response->json('data.0.category'));
        $this->assertEquals('English', $response->json('data.0.language'));
    }

    
    public function test_returns_cached_results_when_available()
    {
        $dto = new RecommendationsDTO(category: 'Gaming');
        $cacheKey = $dto->getTheKey();
        $cachedData = [['id' => 'test-id', 'title' => 'Test Channel']];
        
        Cache::put($cacheKey, json_encode($cachedData), now()->addMinutes(10));

        $response = $this->getJson('/api/recommendations?category=Gaming');

        $response->assertStatus(200);
        $this->assertEquals($cachedData, $response->json('data'));
    }

    
    public function test_validates_sort_key_without_sort_direction()
    {
        $response = $this->getJson('/api/recommendations?sort_key=engagement_rate');

        $response->assertStatus(422);
    }

    
    public function test_validates_sort_direction_without_sort_key()
    {
        $response = $this->getJson('/api/recommendations?sort_direction=asc');

        $response->assertStatus(422);
    }

    
    public function test_validates_invalid_sort_key()
    {
        $response = $this->getJson('/api/recommendations?sort_key=invalid_key&sort_direction=asc');

        $response->assertStatus(422);
    }

    
    public function test_validates_invalid_sort_direction()
    {
        $response = $this->getJson('/api/recommendations?sort_key=engagement_rate&sort_direction=invalid');

        $response->assertStatus(422);
    }

    
    public function test_validates_invalid_last_video_period()
    {
        $response = $this->getJson('/api/recommendations?last_video_period=invalid_period');

        $response->assertStatus(422);
    }

    
    public function test_validates_min_subscribers_as_integer()
    {
        $response = $this->getJson('/api/recommendations?min_subscribers=abc');

        $response->assertStatus(422);
    }

    
    public function test_validates_max_subscribers_as_integer()
    {
        $response = $this->getJson('/api/recommendations?max_subscribers=abc');

        $response->assertStatus(422);
    }
} 