<?php

declare(strict_types=1);

namespace App\Http\Controllers;

abstract class Controller
{
    /**
     * @param bool $status
     * @param string $message
     * @param mixed $data
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function sendResponse(
        bool $status,
        string $message,
        mixed $data = null,
        int $code = 200
    ) {
        return response()->json(
            [
                'status' => $status,
                'message' => $message,
                'data' => $data,
            ],
            $code
        );
    }
}
