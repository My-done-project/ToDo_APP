<?php

namespace App\Traits;

trait ApiResponse
{
    protected function success($data, $message = 'Success', $code = 200)
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    protected function error($message = 'Error', $code = 500)
    {
        return response()->json([
            'status' => false,
            'message' => $message,
        ], $code);
    }
}
