<?php

namespace App\entities\User\Events;

use ebcore\framework\Core\Events;
use ebcore\framework\DB\DbContext;

class UserRegisterEvent extends Events
{
    public function execute(): void
    {
        if ($this->isExecuted('UserRegisterEvent')) {
            return;
        }

        try {
            $user = array();
            $user["name"] = "test1";
            $user["family"] = "test1";
            $user["created_at"] = date("Y/m/d h:i:sa");
            DbContext::User()->create($user);
            $this->markAsExecuted('UserRegisterEvent');
        } catch (\Exception $e) {
            $this->resetExecution('UserRegisterEvent');
            throw $e;
        }
    }
}