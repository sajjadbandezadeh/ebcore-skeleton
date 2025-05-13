<?php

namespace App\entities\User\Events;

use ebcore\framework\Core\Events;
use ebcore\framework\DB\DbContext;

class UserRegisterEvent extends Events
{
    // Event functions must be void
    public function execute(): void
    {
        // $this->isExecuted('UserRegisterEvent') -> boolean result
        if ($this->isExecuted('UserRegisterEvent')) {
            return;
        }

        try {
            $user = array();
            $user["name"] = "test1";
            $user["fam4ily"] = "test1";
            $user["created_at"] = date("Y/m/d h:i:sa");
            DbContext::User()->create($user);
            $this->markAsExecuted('UserRegisterEvent');
        } catch (\Exception $e) {
            $this->resetExecution('UserRegisterEvent');
            throw $e;
        }
    }
}