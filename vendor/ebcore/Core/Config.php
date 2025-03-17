<?php

namespace ebcore\Core;

class Config
{
    private static $instance = null;
    private $config = [];

    private function __construct()
    {
        $this->loadConfigs();
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function loadConfigs()
    {
        $configPath = dirname(dirname(dirname(__DIR__))) . '/config';
        $files = glob($configPath . '/*.json');
        
        foreach ($files as $file) {
            $key = basename($file, '.json');
            $content = file_get_contents($file);
            $this->config[$key] = json_decode($content, true);
        }
    }

    public static function get($key, $default = null)
    {
        $instance = self::getInstance();
        $keys = explode('.', $key);
        $value = $instance->config;

        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }

    public static function set($key, $value)
    {
        $instance = self::getInstance();
        $keys = explode('.', $key);
        $config = &$instance->config;

        while (count($keys) > 1) {
            $key = array_shift($keys);
            if (!isset($config[$key]) || !is_array($config[$key])) {
                $config[$key] = [];
            }
            $config = &$config[$key];
        }

        $config[array_shift($keys)] = $value;
    }
} 