<?php

namespace ebcore\Module;

class StatusManager
{
    private static $instance;
    public $not_found;
    public $ok;
    public $error;

    private function __construct()
    {
        $this->not_found = [
            "code" => 404,
            "message" => "Not Found"
        ];
        $this->ok = [
            "code" => 200,
            "message" => "completed"
        ];
        $this->error = [
            "code" => -1,
            "message" => ""
        ];
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new StatusManager();
        }
        return self::$instance;
    }
}  
