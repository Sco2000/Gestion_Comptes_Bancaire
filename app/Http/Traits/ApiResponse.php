<?php

namespace App\Http\Traits;

trait ApiResponse
{
    public function successResponse($data, $message = '', $code = 200)
    {
        return response()->json([
            'success' => true,
            'data' => $data['data'] ?? $data,
            'pagination' => $data['meta'] ?? null,
            'links' => $data['links'] ?? null,
            'message' => $message,
        ], $code);
    }

    public function errorResponse($message = '', $code = 400)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $code);
    }
}
