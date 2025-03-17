<?php

namespace ebcore\Core;

use ebcore\Module\DataCleaner;
use ebcore\Packages\Logger\Logger;

class Engine
{
    private static $executedEvents = [];
    private static $globalMiddlewares = [];

    public function __construct()
    {
        // Load global middlewares from config
        $configMiddlewares = Config::get('middleware.global_middlewares', []);
        foreach ($configMiddlewares as $middleware) {
            $class = "\\ebcore\\Middlewares\\$middleware";
            if (class_exists($class)) {
                self::$globalMiddlewares[] = $class;
            }
        }
    }

    public function dispatch($handler, $params = [])
    {
        // Execute global middlewares first
        foreach (self::$globalMiddlewares as $middleware) {
            $middleware = new $middleware();
            $middleware->handle();
        }

        // Execute route-specific middlewares
        if (isset($handler['middleware']) && is_array($handler['middleware'])) {
            $modelClass = $handler["model"];
            foreach ($handler['middleware'] as $middlewareName) {
                $class = "\App\\entities\\$modelClass\\Events\\{$middlewareName}";
                if (class_exists($class)) {
                    $middleware = new $class();
                    $middleware->handle();
                }

            }
        }

        return $this->callAction($handler, $params);
    }

    protected function callAction($handler, $params = [])
    {
        $modelClass = $handler["model"];
        $controllerClass = "\App\\entities\\$modelClass\\Controllers\\{$handler['controller']}";
        $actionClass = $handler["action"];
        
        if (!class_exists($controllerClass)) {
            throw new \Exception("Controller `$handler[controller]` not found.");
        }

        if (!method_exists($controllerClass, $actionClass)) {
            throw new \Exception("Method `$actionClass` in Controller `$handler[controller]` not found.");
        }

        if (isset($handler['event'])) {
            $eventClass = "\App\\entities\\$modelClass\\Events\\{$handler['event']}";
            if (!class_exists($eventClass)) {
                throw new \Exception("Event `$handler[event]` not found.");
            }
        }
        
        if (isset($handler["eventTime"])) {
            $eventTime = $handler["eventTime"];
            if (!in_array($eventTime, ['before', 'after'])) {
                throw new \Exception("Event Timer is not valid! (before/after)");
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $requestData = $_POST;
            $params = array_merge($params, $requestData);
        } else if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            $data_cleaner = new DataCleaner();
            $a_data = array();
            $requestData = $data_cleaner->parse_raw_http_request($a_data);
            $params = array_merge($params, $requestData);
        }

        $controllerInstance = new $controllerClass();
        
        if (isset($handler['event']) && isset($eventTime) && $eventTime === 'before') {
            $this->executeEvent($eventClass);
        }

        $result = call_user_func_array([$controllerInstance, $actionClass], $params);

        if (isset($handler['event']) && isset($eventTime) && $eventTime === 'after') {
            $this->executeEvent($eventClass);
        }

        return $result;
    }

    private function executeEvent($eventClass)
    {
        $eventKey = $eventClass . '_' . microtime(true);
        
        if (isset(self::$executedEvents[$eventKey])) {
            return;
        }

        $eventInstance = new $eventClass();
        $eventInstance->execute();
        self::$executedEvents[$eventKey] = true;
    }

    private function processMiddlewares($middlewares, $request)
    {
        $stack = function ($request) {
            return $this->callAction($request);
        };

        // First process entity middlewares (if any)
        foreach (array_reverse($middlewares) as $middleware) {
            $stack = function ($request) use ($stack, $middleware) {
                $middlewareInstance = new $middleware();
                return $middlewareInstance->handle($request, $stack);
            };
        }

        // Then process global middlewares
        foreach (array_reverse(self::$globalMiddlewares) as $middleware) {
            $stack = function ($request) use ($stack, $middleware) {
                $middlewareInstance = new $middleware();
                return $middlewareInstance->handle($request, $stack);
            };
        }

        return $stack($request);
    }

    public function run($request)
    {
        try {
            // Process all middlewares and then call the action
            return $this->processMiddlewares($request->getMiddlewares(), $request);
        } catch (\Exception $e) {
            Logger::error("Error in Engine::run", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}