<?php

namespace App\entities\User\Middlewares;

use ebcore\Middlewares\BaseMiddleware;
use ebcore\Packages\Logger\Logger;

class CheckUserPermissionMiddleware extends BaseMiddleware
{
    private $requiredPermission;

    public function __construct($permission = 'view')
    {
        $this->requiredPermission = $permission;
    }

    public function handle($request, $next)
    {
        // Example: Check if user has required permission
        if (!$this->checkPermission()) {
            Logger::warning("Permission denied for user", [
                'permission' => $this->requiredPermission,
                'user_id' => $_SESSION['user_id'] ?? null
            ]);

            return $this->jsonResponse([
                'error' => 'Permission Denied',
                'message' => 'You do not have the required permission.'
            ], 403);
        }

        return $next($request);
    }

    private function checkPermission()
    {
        // Here you would implement your actual permission checking logic
        // This is just a placeholder
        return isset($_SESSION['user_permissions']) && 
               in_array($this->requiredPermission, $_SESSION['user_permissions']);
    }
} 