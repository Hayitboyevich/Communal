<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BaseController extends Controller
{
    public function sendSuccess($result, $message, $meta = []): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
            'result' => [
                'data' => $result,
            ],
        ];

        if (!empty($meta)) {
            $response['meta'] = $meta;
        }

        return response()->json($response, 200);
    }

    public function sendError($error, $errorMessages = [], $code = 404): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $error,
            'code' => $code,
        ];

        if (!empty($errorMessages)) {
            $response['data'] = $errorMessages;
        }

        return response()->json($response, $code);
    }
}
