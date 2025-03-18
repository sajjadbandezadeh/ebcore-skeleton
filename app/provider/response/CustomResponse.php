<?php

namespace App\provider\response;

use App\provider\response\BaseResponse;

class CustomResponse extends BaseResponse
{
    public function toArray()
    {
        return [
            'message' => $this->message,
            'result' => $this->result,
        ];
    }
}