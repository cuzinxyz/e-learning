<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register(Request $req): JsonResponse
    {
        return $this->sendResponse(status: true, message: 'success', data: [], code: 200);
    }
}
