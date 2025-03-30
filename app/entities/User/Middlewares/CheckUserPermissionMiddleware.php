<?php

namespace App\entities\User\Middlewares;

use ebcore\Middlewares\BaseMiddleware;
use ebcore\Module\Response;
use ebcore\Packages\Logger\Logger;

class CheckUserPermissionMiddleware extends BaseMiddleware
{
    private $requiredPermission;

    public function __construct($permission = 'add-user')
    {
        $this->requiredPermission = $permission;
    }

    private function checkPermission()
    {
        return isset($_SESSION['user_permissions']) &&
               in_array($this->requiredPermission, $_SESSION['user_permissions']);
    }

    protected function process($request, $next)
    {
        $request = $this->getRequest();
        $next = function() { return $this->next(); };

        if (!$this->checkPermission()) {
            Logger::warning("Permission denied for user", [
                'permission' => $this->requiredPermission,
                'user_id' => $_SESSION['user_id'] ?? null
            ]);

            return Response::json(null,'Permission Denied', 403, False);
        }

        return $next($request);
    }
}