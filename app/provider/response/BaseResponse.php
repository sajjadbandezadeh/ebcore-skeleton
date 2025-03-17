<?php

namespace App\provider\response;

class BaseResponse
{
    protected $result;
    protected $message;

    public function __construct($result = null, $message = "Done")
    {
        $this->result = $result;
        $this->message = $message;
    }

    public function toArray()
    {
        return [
            'message' => $this->message,
            'result' => $this->result
        ];
    }

    public function json()
    {
        header('Content-Type: application/json');
        echo json_encode($this->toArray());
        exit();
    }
}
