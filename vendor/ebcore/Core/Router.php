<?php

namespace ebcore\Core;

use ebcore\Core\Engine;
use ebcore\Module\Response;
use ebcore\Module\StatusManager;
use ebcore\Middlewares\DuplicateRequestMiddleware;

class Router
{
    /**
     * @var StatusManager
     */
    private $statusManager;

    public function __construct()
    {
        $this->statusManager = StatusManager::getInstance();
    }

    protected $routes = [];

    public function run()
    {
        // Run global middleware
        $duplicateMiddleware = new DuplicateRequestMiddleware();
        if (!$duplicateMiddleware->handle()) {
            $exception = new Exception();
            $exception->throwException('Duplicate request', 500);
            return;
        }

        $requested_method = $_SERVER['REQUEST_METHOD'];
        $requested_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $clean_uri = $this->cleanUri($requested_uri);

        if (substr($clean_uri, -1) == '/' AND strlen($clean_uri) > 1) {
            $clean_uri = substr($clean_uri, 0, -1);
        }

        // Check if this URI matches any defined route
        $hasMatchingRoute = false;
        foreach ($this->routes[$requested_method] ?? [] as $route => $handler) {
            $pattern = $this->routeToRegex($route);
            if (preg_match($pattern, $clean_uri)) {
                $hasMatchingRoute = true;
                break;
            }
        }

        if (!$hasMatchingRoute) {
            $exception = new Exception();
            $exception->handle404();
        }

        try {
            $result = $this->callEngine($requested_method, $clean_uri);
            return $result;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function map($method, $uri, $model, $controller, $action, $event = null, $eventTime = null, $middleware = null)
    {
        if ($uri[0] != '/') {
            $exception = new Exception();
            $exception->throwException('URI must start with a slash', 500);
        }

        $this->routes[$method][$uri] = [
            'model' => $model,
            'controller' => $controller,
            'action' => $action,
            'event' => $event,
            'eventTime' => $eventTime,
            'middleware' => $middleware,
        ];
    }

    private function callEngine($method, $uri)
    {
        if (substr($uri, -1) == '/' AND strlen($uri) > 1) {
            $updated = substr_replace($uri, '', -1);
            $uri = $updated;
        }
        $matchedRoute = $this->matchRoute($method, $uri);
        
        if ($matchedRoute) {
            $engine = new Engine();
            return $engine->dispatch($matchedRoute['handler'], $matchedRoute['params']);
        } else {
            header('Content-Type: application/json');
            echo json_encode($this->statusManager->not_found);
            exit();
        }
    }

    private function matchRoute($method, $uri)
    {
        foreach ($this->routes[$method] as $route => $handler) {
            $pattern = $this->routeToRegex($route);
            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches);
                return [
                    'handler' => $handler,
                    'params' => $matches
                ];
            }
        }
        return null;
    }

    private function routeToRegex($route)
    {
        $route = preg_quote($route, '/');
        $route = str_replace('\{id\}', '([0-9]+)', $route);
        return "/^$route$/";
    }

    private function cleanUri($uri)
    {
        $scriptName = rtrim($_SERVER['SCRIPT_NAME'], '/');
        $basePath = dirname($scriptName);
        if (strpos($uri, $basePath) === 0) {
            return str_replace($basePath, '', $uri);
        }
        return $uri;
    }
}
