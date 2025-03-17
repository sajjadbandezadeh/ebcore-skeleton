<?php

namespace ebcore\Core;

use ebcore\Module\Response;
use ebcore\Core\Config;

class Exception
{
    private $errorViewPath;
    private $contextLines = 10;

    public function __construct()
    {
        $this->errorViewPath = dirname(dirname(__DIR__)) . '/ebcore/Packages/ErrorHandler/views/error.php';
        
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
        
        $this->render();
    }

    public function render()
    {
        set_exception_handler([$this, 'EbcoreExceptionHandler']);
        set_error_handler([$this, 'EbcoreErrorHandler']);
    }

    public function handle404()
    {
        $error = [
            'type' => '404 Not Found',
            'message' => 'The requested page could not be found.',
            'file' => $_SERVER['SCRIPT_FILENAME'],
            'line' => 0,
            'trace' => (new \Exception())->getTraceAsString(),
            'context' => []
        ];

        http_response_code(404);
        extract(['error' => $error]);
        require $this->errorViewPath;
        exit;
    }

    public function throwException($message, $code, $exception = E_ERROR)
    {
        $error = [
            'type' => 'Fatal Error',
            'message' => $message,
            'file' => '/routes/web.php',
            'line' => 0,
            'trace' => '',
            'context' => []
        ];

        if (Config::get('app.debug', true)) {
            http_response_code($code);
            extract(['error' => $error]);
            require $this->errorViewPath;
        } else {
            header('HTTP/1.1 500 Internal Server Error');
            Response::SystemResponse(500, 'Something went wrong. Please try again later.');
        }
        exit;
    }

    public function EbcoreExceptionHandler($exception)
    {
        $error = [
            'type' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
            'context' => $this->getFileContext($exception->getFile(), $exception->getLine())
        ];

        if (Config::get('app.debug', true)) {
            http_response_code(500);
            extract(['error' => $error]);
            require $this->errorViewPath;
        } else {
            header('HTTP/1.1 500 Internal Server Error');
            Response::SystemResponse(500, 'Something went wrong. Please try again later.');
        }
        exit;
    }

    public function EbcoreErrorHandler($errno, $errstr, $errfile, $errline)
    {
        $error = [
            'type' => $this->getErrorType($errno),
            'message' => $errstr,
            'file' => $errfile,
            'line' => $errline,
            'trace' => (new \Exception())->getTraceAsString(),
            'context' => $this->getFileContext($errfile, $errline)
        ];

        if (Config::get('app.debug', true)) {
            http_response_code(500);
            extract(['error' => $error]);
            require $this->errorViewPath;
        } else {
            header('HTTP/1.1 500 Internal Server Error');
            Response::SystemResponse(500, 'Something went wrong. Please try again later.');
        }
        exit;
    }

    private function getFileContext($file, $line)
    {
        if (!file_exists($file)) {
            return [];
        }

        $lines = file($file);
        $context = [];

        $start = max(0, $line - $this->contextLines - 1);
        $end = min(count($lines), $line + $this->contextLines);

        for ($i = $start; $i < $end; $i++) {
            $context[$i + 1] = rtrim($lines[$i], "\r\n");
        }

        return $context;
    }

    private function getErrorType($errno)
    {
        switch ($errno) {
            case E_ERROR:
                return 'Fatal Error';
            case E_WARNING:
                return 'Warning';
            case E_PARSE:
                return 'Parse Error';
            case E_NOTICE:
                return 'Notice';
            case E_CORE_ERROR:
                return 'Core Error';
            case E_CORE_WARNING:
                return 'Core Warning';
            case E_COMPILE_ERROR:
                return 'Compile Error';
            case E_COMPILE_WARNING:
                return 'Compile Warning';
            case E_USER_ERROR:
                return 'User Error';
            case E_USER_WARNING:
                return 'User Warning';
            case E_USER_NOTICE:
                return 'User Notice';
            case E_STRICT:
                return 'Strict Standards';
            case E_RECOVERABLE_ERROR:
                return 'Recoverable Error';
            case E_DEPRECATED:
                return 'Deprecated';
            case E_USER_DEPRECATED:
                return 'User Deprecated';
            default:
                return 'Unknown Error';
        }
    }
}

new Exception();