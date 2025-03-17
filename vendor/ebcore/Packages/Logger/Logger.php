<?php

namespace ebcore\Packages\Logger;

class Logger
{
    private static $logPath = null;
    private static $logLevels = [
        'emergency' => 0,
        'alert'     => 1,
        'critical'  => 2,
        'error'     => 3,
        'warning'   => 4,
        'notice'    => 5,
        'info'      => 6,
        'debug'     => 7
    ];

    private static function init()
    {
        if (self::$logPath === null) {
            $rootPath = dirname(dirname(dirname(dirname(__DIR__))));
            self::$logPath = $rootPath . '/storage/logs';
            
            if (!is_dir(self::$logPath)) {
                mkdir(self::$logPath, 0777, true);
            }
        }
    }

    private static function getLogFile()
    {
        self::init();
        return self::$logPath . '/' . date('Y-m-d') . '.log';
    }

    private static function write($level, $message, array $context = [])
    {
        $logFile = self::getLogFile();
        $timestamp = date('Y-m-d H:i:s');
        
        $contextStr = !empty($context) ? ' ' . json_encode($context) : '';
        
        $logMessage = "[{$timestamp}] {$level}: {$message}{$contextStr}" . PHP_EOL;
        
        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }

    public static function emergency($message, array $context = [])
    {
        self::write('EMERGENCY', $message, $context);
    }

    public static function alert($message, array $context = [])
    {
        self::write('ALERT', $message, $context);
    }

    public static function critical($message, array $context = [])
    {
        self::write('CRITICAL', $message, $context);
    }

    public static function error($message, array $context = [])
    {
        self::write('ERROR', $message, $context);
    }

    public static function warning($message, array $context = [])
    {
        self::write('WARNING', $message, $context);
    }

    public static function notice($message, array $context = [])
    {
        self::write('NOTICE', $message, $context);
    }

    public static function info($message, array $context = [])
    {
        self::write('INFO', $message, $context);
    }

    public static function debug($message, array $context = [])
    {
        self::write('DEBUG', $message, $context);
    }
}
