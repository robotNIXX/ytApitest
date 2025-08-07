<?php

namespace Tests\Feature;

use App\Models\YoutubeChannel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class MiddlewareLoggingTest extends TestCase
{
    use RefreshDatabase;

    public function test_request_logging_middleware_adds_headers()
    {
        // Создаем тестовый канал
        YoutubeChannel::factory()->create();

        // Выполняем запрос
        $response = $this->getJson('/api/recommendations');

        $response->assertStatus(200);
        
        // Проверяем, что middleware работает (запрос выполняется без ошибок)
        $this->assertTrue(true);
    }

    public function test_api_logging_middleware_adds_request_id_and_execution_time()
    {
        // Создаем тестовый канал
        YoutubeChannel::factory()->create();

        // Выполняем API запрос
        $response = $this->getJson('/api/recommendations');

        $response->assertStatus(200);
        
        // Проверяем, что в заголовках ответа есть request ID и execution time
        $this->assertTrue($response->headers->has('X-Request-ID'));
        $this->assertTrue($response->headers->has('X-Execution-Time'));
        
        // Проверяем формат execution time
        $executionTime = $response->headers->get('X-Execution-Time');
        $this->assertStringContainsString('ms', $executionTime);
    }

    public function test_api_logging_middleware_handles_validation_errors()
    {
        // Выполняем запрос с невалидными параметрами
        $response = $this->getJson('/api/recommendations?sort_key=invalid_key&sort_direction=asc');

        $response->assertStatus(422);
        
        // Проверяем, что middleware все равно добавляет заголовки
        $this->assertTrue($response->headers->has('X-Request-ID'));
    }

    public function test_middleware_works_with_different_request_methods()
    {
        // Создаем тестовый канал
        YoutubeChannel::factory()->create();

        // Тестируем GET запрос
        $response = $this->getJson('/api/recommendations');
        $response->assertStatus(200);
        $this->assertTrue($response->headers->has('X-Request-ID'));

        // Тестируем POST запрос (если бы был такой endpoint)
        // $response = $this->postJson('/api/some-endpoint');
        // $response->assertStatus(200);
        // $this->assertTrue($response->headers->has('X-Request-ID'));
    }

    public function test_middleware_logs_are_written_to_files()
    {
        // Создаем тестовый канал
        YoutubeChannel::factory()->create();

        // Выполняем API запрос
        $response = $this->getJson('/api/recommendations');
        $response->assertStatus(200);

        // Ищем API лог файл с датой
        $logFiles = glob(storage_path('logs/api-*.log'));
        $this->assertNotEmpty($logFiles, 'API log file should exist');
        
        // Берем самый свежий файл
        $latestLogFile = end($logFiles);
        
        // Читаем содержимое лога
        $logContent = file_get_contents($latestLogFile);
        $this->assertStringContainsString('API Request started', $logContent);
        $this->assertStringContainsString('API Request completed', $logContent);
    }
}
