<?php

namespace ebcore\Middlewares;

use ebcore\Core\Config;
use ebcore\Core\Cache;
use ebcore\Packages\Logger\Logger;

class ThrottleMiddleware extends BaseMiddleware
{
    private $maxRequests;
    private $decayMinutes;

    public function __construct()
    {
        Cache::initialize();
        
        // Set default values if config is not set
        $this->maxRequests = (int)Config::get('middleware.throttle.max_requests', 60);
        $this->decayMinutes = (int)Config::get('middleware.throttle.decay_minutes', 1);

        if ($this->maxRequests <= 0) {
            $this->maxRequests = 60;
        }
        
        if ($this->decayMinutes <= 0) {
            $this->decayMinutes = 1;
        }
    }

    protected function process($request, $next)
    {
        $key = $this->resolveRequestSignature($request);

        // Cleanup old cache entries
        Cache::cleanup();
        
        if ($this->tooManyAttempts($key)) {
            Logger::warning("Rate limit exceeded", [
                'ip' => $this->getClientIp(),
                'key' => $key,
                'max_attempts' => $this->maxRequests
            ]);

            header('HTTP/1.1 429 Too Many Requests');
            header('Retry-After: ' . ($this->decayMinutes * 60));
            
            echo json_encode([
                'error' => 'Too Many Attempts.',
                'message' => 'Please try again later.',
                'retry_after' => $this->decayMinutes * 60
            ]);
            exit();
        }

        $this->incrementAttempts($key);
        return $next($request);
    }

    private function resolveRequestSignature($request)
    {
        return sha1(
            $this->getClientIp() .
            ($request['uri'] ?? $_SERVER['REQUEST_URI'])
        );
    }

    private function tooManyAttempts($key)
    {
        $attempts = Cache::get($key);
        return $attempts !== null && $attempts >= $this->maxRequests;
    }

    private function incrementAttempts($key)
    {
        $ttl = $this->decayMinutes * 60;
        
        if (!Cache::has($key)) {
            Cache::put($key, 1, $ttl);
            return;
        }
        
        Cache::increment($key, 1);
    }

    protected function getClientIp()
    {
        return $_SERVER['HTTP_X_FORWARDED_FOR'] ?? 
               $_SERVER['HTTP_CLIENT_IP'] ?? 
               $_SERVER['REMOTE_ADDR'];
    }
}