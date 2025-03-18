<?php

namespace App\provider\mappers\UserMappers;

class UserIndexMapper
{
    public static function mapUserCollection(array $users)
    {
        return array_map(function($user) {
            return [
                'name' => $user['name'],
            ];
        }, $users);
    }
}