<?php

namespace App\entities\User\Middlewares;

use ebcore\framework\Middlewares\BaseMiddleware;
use ebcore\framework\Module\Response;
use ebcore\framework\Packages\Logger\Logger;

class CheckUserPermissionMiddleware extends BaseMiddleware
{
    private $requiredPermission;

    public function __construct($permission = 'view')
    {
        $this->requiredPermission = $permission;
    }

    public function handle()
    {
        // Example: Check if the user has the required permission
        if ($this->checkPermission()) {
            Logger::warning("Permission denied for user", [
                'permission' => $this->requiredPermission,
                'user_id' => $_SESSION['user_id'] ?? null
            ]);
            // Do not manually return any response; responses will be handled automatically.
            Response::json(null, "You do not have the required permission", 403, false); // do not return response | response will return auto
            exit(); // IMPORTANT
        }

        return parent::next();
    }

    private function checkPermission()
    {
        return isset($_SESSION['user_permissions']) &&
            in_array($this->requiredPermission, $_SESSION['user_permissions']);
    }
}