<?php

/**
 * Ebcore Framework - A Modern PHP Framework
 *
 * @package  Ebcore
 * @author   Sajjad Bandezadeh
 * @version  1.0.0
 */

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| this application. We just need to utilize it! We'll simply require it
| into the script here so we don't need to manually load our classes.
|
*/

define('ROOT_PATH', dirname(__DIR__));

/*
|--------------------------------------------------------------------------
| Check If The Application Is Under Maintenance
|--------------------------------------------------------------------------
|
| If the application is in maintenance / demo mode via the "down" command
| we will load this file so that any pre-rendered content can be shown
| instead of starting the framework, which could cause an exception.
|
*/

if (file_exists(ROOT_PATH . '/storage/framework/maintenance.php')) {
    require ROOT_PATH . '/storage/framework/maintenance.php';
    exit;
}

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| We'll register the autoloader to load classes automatically. This will
| allow us to not have to manually require class files before using them.
|
*/

spl_autoload_register(function ($class) {
    $class = str_replace('\\', '/', $class);
    // Check in vendor directory
    $vendorPath = ROOT_PATH . '/vendor/' . $class . '.php';
    if (file_exists($vendorPath)) {
        require_once $vendorPath;
        return;
    }
    // Check in app directory

    $appPath = ROOT_PATH . '/' . $class . '.php';
    if (file_exists($appPath)) {
        require_once $appPath;
        return;
    }
});

/*
|--------------------------------------------------------------------------
| Initialize Asset Handler
|--------------------------------------------------------------------------
|
| Here we will initialize our asset handler that will securely serve static
| files for our error pages. This ensures that assets are served safely
| without exposing the vendor directory structure.
|
*/
//
//if (!class_exists(ebcore\Packages\ErrorHandler\AssetHandler::class)) {
//    echo "Ebcore: Make sure you have run composer install with a php version 8.1 or above";
//    exit();
//}

ebcore\framework\Packages\ErrorHandler\AssetHandler::init();

/*
|--------------------------------------------------------------------------
| Initialize Error Handling
|--------------------------------------------------------------------------
|
| Here we will initialize our custom error handling system that will provide
| a better error experience for our users. This will catch all errors and
| exceptions and display them in a user-friendly way.
|
*/

new ebcore\framework\Core\Exception();

/*
|--------------------------------------------------------------------------
| Load The Application Configuration
|--------------------------------------------------------------------------
|
| Here we will load the application configuration. This will give us access
| to all of the configuration values that have been defined in the config
| directory of our application.
|
*/

$config = ebcore\framework\Core\Config::getInstance();

/*
|--------------------------------------------------------------------------
| Load The Application Routes
|--------------------------------------------------------------------------
|
| Here we will load the application routes. These routes are defined in the
| routes directory and will determine how the application handles requests.
|
*/

require_once ROOT_PATH . '/' . '/routes/web.php';