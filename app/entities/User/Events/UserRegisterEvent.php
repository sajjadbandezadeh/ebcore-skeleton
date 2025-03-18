<?php

namespace App\entities\User\Events;

use ebcore\Core\Events;
use ebcore\DB\DbContext;
use ebcore\Module\Response;

class UserRegisterEvent extends Events
{
    public function execute()
    {
        if ($this->isExecuted('UserRegisterEvent')) {
            return;
        }

        try {
            $user = array();
            $user["name"] = "test";
            $user["family"] = "test";
            $user["created_at"] = date("Y/m/d h:i:sa");
            DbContext::User()->create($user);
            $this->markAsExecuted('UserRegisterEvent');
        } catch (\Exception $e) {
            $this->resetExecution('UserRegisterEvent');
            throw $e;
        }
    }
}