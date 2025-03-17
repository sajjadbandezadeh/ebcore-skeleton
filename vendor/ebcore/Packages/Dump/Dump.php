<?php

namespace ebcore\Packages\Dump;

class Dump
{
    private static $styles = [
        'background' => '#1e1e1e',
        'color' => '#ffffff',
        'font-family' => 'Consolas, Monaco, "Courier New", monospace',
        'font-size' => '14px',
        'line-height' => '1.5',
        'padding' => '20px',
        'border-radius' => '4px',
        'box-shadow' => '0 2px 4px rgba(0,0,0,0.2)',
        'margin' => '20px 0',
        'max-width' => '100%',
        'overflow-x' => 'auto'
    ];

    private static $typeColors = [
        'string' => '#ce9178',
        'integer' => '#b5cea8',
        'float' => '#b5cea8',
        'boolean' => '#569cd6',
        'null' => '#569cd6',
        'array' => '#9cdcfe',
        'object' => '#4ec9b0',
        'resource' => '#c586c0',
        'callable' => '#dcdcaa',
        'unknown' => '#d4d4d4'
    ];

    public static function dd(...$vars)
    {
        if (php_sapi_name() === 'cli') {
            self::dumpCli($vars);
        } else {
            self::dumpHtml($vars);
        }
        die(1);
    }

    public static function dump(...$vars)
    {
        if (php_sapi_name() === 'cli') {
            self::dumpCli($vars);
        } else {
            self::dumpHtml($vars);
        }
    }

    private static function dumpCli($vars)
    {
        foreach ($vars as $var) {
            print_r($var);
            echo PHP_EOL;
        }
    }

    private static function dumpHtml($vars)
    {
        $style = self::generateStyle();
        echo "<pre style='{$style}'>";
        
        foreach ($vars as $var) {
            self::formatVar($var);
        }
        
        echo "</pre>";
    }

    private static function generateStyle()
    {
        $style = '';
        foreach (self::$styles as $property => $value) {
            $style .= "{$property}: {$value}; ";
        }
        return $style;
    }

    private static function formatVar($var, $depth = 0)
    {
        $type = gettype($var);
        $color = self::$typeColors[$type] ?? self::$typeColors['unknown'];
        
        echo "<span style='color: {$color}'>";
        
        switch ($type) {
            case 'string':
                echo "string(" . strlen($var) . ") \"{$var}\"";
                break;
                
            case 'integer':
            case 'float':
                echo "{$type}({$var})";
                break;
                
            case 'boolean':
                echo "bool(" . ($var ? 'true' : 'false') . ")";
                break;
                
            case 'null':
                echo "null";
                break;
                
            case 'array':
                self::formatArray($var, $depth);
                break;
                
            case 'object':
                self::formatObject($var, $depth);
                break;
                
            case 'resource':
                echo "resource(" . get_resource_type($var) . ")";
                break;
                
            case 'callable':
                echo "callable";
                break;
                
            default:
                echo "unknown type";
        }
        
        echo "</span>";
    }

    private static function formatArray($array, $depth)
    {
        echo "array(" . count($array) . ") {\n";
        
        foreach ($array as $key => $value) {
            echo str_repeat("    ", $depth + 1);
            echo "[";
            
            if (is_string($key)) {
                echo "\"{$key}\"";
            } else {
                echo $key;
            }
            
            echo "] => ";
            self::formatVar($value, $depth + 1);
            echo "\n";
        }
        
        echo str_repeat("    ", $depth) . "}";
    }

    private static function formatObject($object, $depth)
    {
        $class = get_class($object);
        echo "object({$class}) {\n";
        
        $reflection = new \ReflectionObject($object);
        foreach ($reflection->getProperties() as $property) {
            $property->setAccessible(true);
            echo str_repeat("    ", $depth + 1);
            echo "public ";
            echo "\"{$property->getName()}\" => ";
            self::formatVar($property->getValue($object), $depth + 1);
            echo "\n";
        }
        
        echo str_repeat("    ", $depth) . "}";
    }
} 