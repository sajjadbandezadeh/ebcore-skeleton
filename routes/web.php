<?php

use ebcore\framework\Core\Router;

require_once ROOT_PATH . '/vendor/ebcore/framework/Core/Exception.php';

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

$router = new Router();

// Define your routes here

$router->map('GET', '/', 'User', 'UserController', 'index');
$router->map('GET', '/users', 'User', 'UserController', 'index', 'UserRegisterEvent', 'after');
$router->map('GET', '/users/{id}', 'User', 'UserController', 'getById');
$router->map('GET', '/users/customResponse', 'User', 'UserController', 'indexCustomResponse');
$router->map('GET', '/users/customData', 'User', 'UserController', 'indexCustomData');

$router->map('POST', '/users/create', 'User', 'UserController', 'create');
$router->map('PUT', '/users/{id}', 'User', 'UserController', 'edit');
$router->map('DELETE', '/users/{id}', 'User', 'UserController', 'delete', null, null, ['CheckUserPermissionMiddleware']);

$router->run();
