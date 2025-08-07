<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ApiLoggingMiddleware
{
    /**
     * Handle an incoming API request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Засекаем время начала запроса
        $startTime = microtime(true);
        $requestId = uniqid('api_', true);
        
        // Добавляем request ID в заголовки для отслеживания
        $request->headers->set('X-Request-ID', $requestId);
        
        // Логируем входящий API запрос
        Log::channel('api')->info('API Request started', [
            'request_id' => $requestId,
            'method' => $request->method(),
            'endpoint' => $request->path(),
            'full_url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'content_type' => $request->header('Content-Type'),
            'accept' => $request->header('Accept'),
            'authorization' => $request->header('Authorization') ? 'Bearer ***' : null,
            'query_params' => $request->query->all(),
            'request_body' => $this->sanitizeRequestBody($request->all()),
            'user_id' => $request->user()?->id,
        ]);

        try {
            // Выполняем запрос
            $response = $next($request);
            
            // Вычисляем время выполнения
            $endTime = microtime(true);
            $executionTime = round(($endTime - $startTime) * 1000, 2);
            
            // Добавляем request ID в заголовки ответа
            $response->headers->set('X-Request-ID', $requestId);
            $response->headers->set('X-Execution-Time', $executionTime . 'ms');
            
            // Логируем успешный API ответ
            Log::channel('api')->info('API Request completed', [
                'request_id' => $requestId,
                'method' => $request->method(),
                'endpoint' => $request->path(),
                'status_code' => $response->getStatusCode(),
                'execution_time_ms' => $executionTime,
                'response_size' => strlen($response->getContent()),
                'memory_usage' => round(memory_get_peak_usage(true) / 1024 / 1024, 2) . ' MB',
                'response_headers' => $response->headers->all(),
            ]);
            
            return $response;
            
        } catch (\Exception $e) {
            // Вычисляем время выполнения до ошибки
            $endTime = microtime(true);
            $executionTime = round(($endTime - $startTime) * 1000, 2);
            
            // Логируем ошибку API
            Log::channel('api')->error('API Request failed', [
                'request_id' => $requestId,
                'method' => $request->method(),
                'endpoint' => $request->path(),
                'error' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'execution_time_ms' => $executionTime,
                'memory_usage' => round(memory_get_peak_usage(true) / 1024 / 1024, 2) . ' MB',
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Очищает чувствительные данные из тела запроса
     */
    private function sanitizeRequestBody(array $data): array
    {
        $sensitiveFields = ['password', 'password_confirmation', 'token', 'api_key', 'secret'];
        
        return array_map(function ($value) use ($sensitiveFields) {
            if (is_array($value)) {
                return $this->sanitizeRequestBody($value);
            }
            
            foreach ($sensitiveFields as $field) {
                if (stripos($value, $field) !== false) {
                    return '***HIDDEN***';
                }
            }
            
            return $value;
        }, $data);
    }
}
