<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RequestLoggingMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Засекаем время начала запроса
        $startTime = microtime(true);
        
        // Логируем входящий запрос
        Log::info('Incoming request', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'headers' => $request->headers->all(),
            'query_params' => $request->query->all(),
            'request_body' => $request->except(['password', 'password_confirmation']), // Исключаем чувствительные данные
        ]);

        // Выполняем запрос
        $response = $next($request);

        // Вычисляем время выполнения
        $endTime = microtime(true);
        $executionTime = round(($endTime - $startTime) * 1000, 2); // в миллисекундах

        // Логируем ответ
        Log::info('Request completed', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'status_code' => $response->getStatusCode(),
            'execution_time_ms' => $executionTime,
            'response_size' => strlen($response->getContent()),
            'memory_usage' => round(memory_get_peak_usage(true) / 1024 / 1024, 2) . ' MB',
        ]);

        return $response;
    }
}
