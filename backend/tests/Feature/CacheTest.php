<?php

namespace Tests\Feature;

use App\DTO\RecommendationsDTO;
use App\Models\YoutubeChannel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CacheTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    
    public function test_caches_results_for_same_parameters()
    {
        YoutubeChannel::factory()->count(3)->create();

        // Первый запрос - должен получить данные из БД и закэшировать
        $response1 = $this->getJson('/api/recommendations?category=Gaming');
        $response1->assertStatus(200);

        // Проверяем, что данные закэшированы
        $dto = new RecommendationsDTO(category: 'Gaming');
        $cacheKey = $dto->getTheKey();
        $this->assertTrue(Cache::has($cacheKey));

        // Второй запрос - должен получить данные из кэша
        $response2 = $this->getJson('/api/recommendations?category=Gaming');
        $response2->assertStatus(200);

        // Результаты должны быть одинаковыми
        $this->assertEquals($response1->json('data'), $response2->json('data'));
    }

    
    public function test_uses_different_cache_keys_for_different_parameters()
    {
        YoutubeChannel::factory()->count(3)->create();

        // Первый запрос
        $response1 = $this->getJson('/api/recommendations?category=Gaming');
        $response1->assertStatus(200);

        // Второй запрос с другими параметрами
        $response2 = $this->getJson('/api/recommendations?category=Music');
        $response2->assertStatus(200);

        // Проверяем, что используются разные ключи кэша
        $dto1 = new RecommendationsDTO(category: 'Gaming');
        $dto2 = new RecommendationsDTO(category: 'Music');
        
        $this->assertTrue(Cache::has($dto1->getTheKey()));
        $this->assertTrue(Cache::has($dto2->getTheKey()));
        $this->assertNotEquals($dto1->getTheKey(), $dto2->getTheKey());
    }

    
    public function test_uses_different_cache_keys_for_different_combinations()
    {
        YoutubeChannel::factory()->count(3)->create();

        // Запрос с одним параметром
        $response1 = $this->getJson('/api/recommendations?category=Gaming');
        $response1->assertStatus(200);

        // Запрос с двумя параметрами
        $response2 = $this->getJson('/api/recommendations?category=Gaming&language=en');
        $response2->assertStatus(200);

        // Запрос с тремя параметрами
        $response3 = $this->getJson('/api/recommendations?category=Gaming&language=en&min_subscribers=1000');
        $response3->assertStatus(200);

        // Проверяем, что все ключи разные
        $dto1 = new RecommendationsDTO(category: 'Gaming');
        $dto2 = new RecommendationsDTO(category: 'Gaming', language: 'en');
        $dto3 = new RecommendationsDTO(category: 'Gaming', language: 'en', min_subscribers: 1000);

        $this->assertNotEquals($dto1->getTheKey(), $dto2->getTheKey());
        $this->assertNotEquals($dto2->getTheKey(), $dto3->getTheKey());
        $this->assertNotEquals($dto1->getTheKey(), $dto3->getTheKey());
    }

    
    public function test_returns_cached_data_when_available()
    {
        // Создаем тестовые данные в кэше
        $dto = new RecommendationsDTO(category: 'Gaming');
        $cacheKey = $dto->getTheKey();
        $cachedData = [
            [
                'id' => 'test-id-1',
                'title' => 'Test Channel 1',
                'category' => 'Gaming'
            ],
            [
                'id' => 'test-id-2',
                'title' => 'Test Channel 2',
                'category' => 'Gaming'
            ]
        ];
        
        Cache::put($cacheKey, json_encode($cachedData), now()->addMinutes(10));

        // Запрос должен вернуть закэшированные данные
        $response = $this->getJson('/api/recommendations?category=Gaming');
        
        $response->assertStatus(200);
        $this->assertEquals($cachedData, $response->json('data'));
    }

    
    public function test_does_not_query_database_when_cache_hit()
    {
        // Создаем тестовые данные в кэше
        $dto = new RecommendationsDTO(category: 'Gaming');
        $cacheKey = $dto->getTheKey();
        $cachedData = [['id' => 'test-id', 'title' => 'Test Channel']];
        
        Cache::put($cacheKey, json_encode($cachedData), now()->addMinutes(10));

        // Создаем канал в БД (но он не должен быть запрошен)
        YoutubeChannel::factory()->create(['category' => 'Gaming']);

        // Запрос должен вернуть закэшированные данные, а не данные из БД
        $response = $this->getJson('/api/recommendations?category=Gaming');
        
        $response->assertStatus(200);
        $this->assertEquals($cachedData, $response->json('data'));
    }

    
    public function test_caches_empty_results()
    {
        // Запрос без фильтров, но без данных в БД
        $response1 = $this->getJson('/api/recommendations');
        $response1->assertStatus(200);

        // Проверяем, что пустой результат закэширован
        $dto = new RecommendationsDTO();
        $cacheKey = $dto->getTheKey();
        $this->assertTrue(Cache::has($cacheKey));

        // Второй запрос должен вернуть закэшированный пустой результат
        $response2 = $this->getJson('/api/recommendations');
        $response2->assertStatus(200);
        $this->assertEquals($response1->json('data'), $response2->json('data'));
    }

    
    public function test_handles_cache_miss_and_stores_result()
    {
        YoutubeChannel::factory()->create(['category' => 'Gaming']);

        // Первый запрос - кэш промах, данные из БД
        $response1 = $this->getJson('/api/recommendations?category=Gaming');
        $response1->assertStatus(200);

        // Проверяем, что результат закэширован
        $dto = new RecommendationsDTO(category: 'Gaming');
        $cacheKey = $dto->getTheKey();
        $this->assertTrue(Cache::has($cacheKey));

        // Второй запрос - кэш попадание
        $response2 = $this->getJson('/api/recommendations?category=Gaming');
        $response2->assertStatus(200);

        // Результаты должны быть одинаковыми
        $this->assertEquals($response1->json('data'), $response2->json('data'));
    }

    
    public function test_uses_correct_cache_key_for_complex_parameters()
    {
        YoutubeChannel::factory()->count(3)->create();

        $response = $this->getJson('/api/recommendations?' . http_build_query([
            'category' => 'Gaming',
            'min_subscribers' => 1000,
            'max_subscribers' => 10000,
            'language' => 'English',
            'region' => 'USA',
            'last_video_period' => 'last_7_days',
            'sort_key' => 'engagement_rate',
            'sort_direction' => 'desc'
        ]));

        $response->assertStatus(200);

        $dto = new RecommendationsDTO(
            category: 'Gaming',
            min_subscribers: 1000,
            max_subscribers: 10000,
            language: 'English',
            region: 'USA',
            last_video_period: 'last_7_days',
            sort_key: 'engagement_rate',
            sort_direction: 'desc'
        );

        $cacheKey = $dto->getTheKey();
        $this->assertTrue(Cache::has($cacheKey));
    }

    
    public function test_handles_cache_expiration()
    {
        YoutubeChannel::factory()->create(['category' => 'Gaming']);

        // Первый запрос
        $response1 = $this->getJson('/api/recommendations?category=Gaming');
        $response1->assertStatus(200);

        // Проверяем, что данные закэшированы
        $dto = new RecommendationsDTO(category: 'Gaming');
        $cacheKey = $dto->getTheKey();
        $this->assertTrue(Cache::has($cacheKey));

        // Очищаем кэш (симулируем истечение срока действия)
        Cache::flush();

        // Второй запрос после истечения кэша
        $response2 = $this->getJson('/api/recommendations?category=Gaming');
        $response2->assertStatus(200);

        // Данные должны быть получены из БД снова
        $this->assertTrue(Cache::has($cacheKey));
    }
} 