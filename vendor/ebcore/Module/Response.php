<?php

namespace ebcore\Module;

class Response
{
    public static function json($data = null, $message = "done", $code = 200, $status = true)
    {
        $response = [
            'code' => $code,
            'message' => $message,
            'status' => $status,
            'data' => $data,
        ];
        header('Content-Type: application/json');
        echo json_encode($response);
    }
    
    public static function SystemResponse($code,$message)
    {
        $response = [
            'code' => $code,
            'message' => $message,
        ];
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }
}