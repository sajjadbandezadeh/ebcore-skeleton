<?php

namespace ebcore\Middlewares;

abstract class BaseMiddleware
{
    /**
     * Handle the incoming request.
     *
     * @param mixed $request
     * @param callable $next
     * @return mixed
     */
    public function handle($request = null, $next = null)
    {
        if ($request === null) {
            $request = $this->getRequest();
        }
        
        if ($next === null) {
            $next = function() { return $this->next(); };
        }

        return $this->process($request, $next);
    }

    /**
     * Send JSON response
     *
     * @param array $data
     * @param int $status
     * @return void
     */
    protected function jsonResponse($data, $status = 200)
    {
        header('Content-Type: application/json');
        http_response_code($status);
        echo json_encode($data);
        exit();
    }

    /**
     * Get request headers
     *
     * @return array
     */
    protected function getHeaders()
    {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))))] = $value;
            }
        }
        return $headers;
    }

    /**
     * Get client IP address
     *
     * @return string
     */
    protected function getClientIp()
    {
        return $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['HTTP_CLIENT_IP'] ?? $_SERVER['REMOTE_ADDR'];
    }

    protected function getRequest()
    {
        return [
            'method' => $_SERVER['REQUEST_METHOD'],
            'uri' => $_SERVER['REQUEST_URI'],
            'params' => array_merge($_GET, $_POST),
            'headers' => getallheaders()
        ];
    }

    protected function next()
    {
        return true;
    }

    abstract protected function process($request, $next);
} 