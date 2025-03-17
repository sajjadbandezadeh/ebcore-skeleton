<?php

namespace ebcore\Packages\ErrorHandler;

class AssetHandler {
    private static $allowedExtensions = ['css', 'js'];
    private static $basePath;

    public static function init() {
        self::$basePath = dirname(__FILE__) . '\assets';
        self::handleRequest();
    }

    public static function handleRequest() {
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        // Check if request starts with /_error/assets/ using strict comparison
        if (strpos($requestUri, '/_error/assets/') !== false && strpos($requestUri, '/_error/assets/') === 0) {
            $filePath = self::getFilePath($requestUri);
            if ($filePath && file_exists($filePath)) {
                self::serveFile($filePath);
                exit;
            }
            // Return 404 if file not found
            header('HTTP/1.0 404 Not Found');
            exit('File not found');
        }
    }

    private static function getFilePath($uri) {
        $relativePath = str_replace('/_error/assets/', '', $uri);
        $extension = pathinfo($relativePath, PATHINFO_EXTENSION);

        // Security checks
        if (!in_array($extension, self::$allowedExtensions)) {
            return false;
        }

        $fullPath = self::$basePath . '/' . $relativePath;
        $realPath = realpath($fullPath);
        // Prevent directory traversal
        if ($realPath === false || strpos($realPath, self::$basePath) !== 0) {
            return false;
        }

        return $realPath;
    }

    private static function serveFile($filePath) {
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $mimeTypes = [
            'css' => 'text/css',
            'js' => 'application/javascript'
        ];

        if (!isset($mimeTypes[$extension])) {
            header('HTTP/1.0 415 Unsupported Media Type');
            exit('Unsupported file type');
        }

        header('Content-Type: ' . $mimeTypes[$extension]);
        header('Cache-Control: public, max-age=31536000'); // Cache for 1 year
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
    }
} 