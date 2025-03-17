<?php

namespace ebcore\DB;

class DbContext
{
    private static $instances = [];

    public static function __callStatic($name, $arguments)
    {
        $modelClass = "\App\\entities\\" . ucfirst($name) . "\\" . ucfirst($name);
        
        if (!class_exists($modelClass)) {
            throw new \Exception("Model class $modelClass not found.");
        }

        if (!isset(self::$instances[$modelClass])) {
            self::$instances[$modelClass] = new $modelClass();
        }

        return self::$instances[$modelClass];
    }
}