<?php

namespace ebcore\Middlewares;

class DuplicateRequestMiddleware extends BaseMiddleware
{
    private static $processedRequests = [];
    private static $sessionKey = 'processed_requests';
    private $ignoredExtensions = ['css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'ico'];

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION[self::$sessionKey])) {
            $_SESSION[self::$sessionKey] = [];
        }

        // Cleanup old entries
        $this->cleanup();
    }

    protected function process($request, $next)
    {
        // Check if it's a static file request
        if ($this->isStaticFile($request['uri'])) {
            return $next($request);
        }

        $signature = $this->generateRequestSignature($request);

        if ($this->isDuplicateRequest($signature)) {
            header('HTTP/1.1 429 Too Many Requests');
            return json_encode(['error' => 'Duplicate request detected']);
        }

        $this->markRequestAsProcessed($signature);
        return $next($request);
    }

    private function generateRequestSignature($request)
    {
        return sha1(
            $request['method'] .
            $request['uri'] .
            json_encode($request['params']) .
            ($_SERVER['HTTP_USER_AGENT'] ?? '') .
            ($_SERVER['HTTP_ACCEPT'] ?? '') .
            ($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '')
        );
    }

    private function isDuplicateRequest($signature)
    {
        return isset(self::$processedRequests[$signature]) || 
               isset($_SESSION[self::$sessionKey][$signature]);
    }

    private function markRequestAsProcessed($signature)
    {
        $timestamp = time();
        self::$processedRequests[$signature] = $timestamp;
        $_SESSION[self::$sessionKey][$signature] = $timestamp;
    }

    private function cleanup()
    {
        $now = time();
        $timeout = 60; // 1 minute timeout

        // Cleanup memory cache
        foreach (self::$processedRequests as $signature => $timestamp) {
            if ($now - $timestamp > $timeout) {
                unset(self::$processedRequests[$signature]);
            }
        }

        // Cleanup session cache
        foreach ($_SESSION[self::$sessionKey] as $signature => $timestamp) {
            if ($now - $timestamp > $timeout) {
                unset($_SESSION[self::$sessionKey][$signature]);
            }
        }
    }

    private function isStaticFile($uri)
    {
        $extension = strtolower(pathinfo($uri, PATHINFO_EXTENSION));
        return in_array($extension, $this->ignoredExtensions);
    }
} 