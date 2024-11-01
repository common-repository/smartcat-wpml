<?php

namespace Smartcat\Includes\Controllers;

class Controller
{
    protected function responseOk($data = [])
    {
        wp_send_json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    protected function responseFailed($message = 'No message')
    {
        wp_send_json([
            'status' => 'failed',
            'message' => $message
        ]);
    }
}