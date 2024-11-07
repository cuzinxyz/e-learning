<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Constants\AuthenticationCode;
use App\Constants\Messages;
use App\Events\Auth\UserCreated;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\Auth\AuthService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * @param RegisterRequest $req
     * @return JsonResponse
     * @throws Exception
     */
    public function register(RegisterRequest $req): JsonResponse
    {
        try {
            DB::beginTransaction();

            $userCreated = $this->authService->create($req->validated());

            $authCode = $this->authService->authenticationCode(
                $userCreated,
                AuthenticationCode::REGISTER
            );

            DB::commit();

            event(new UserCreated(data: [
                'code' => $authCode,
                'template' => 'email.authenticate',
                'email' => $req->email,
                'subject' => __('auth.subject_authentication'),
            ]));

            return $this->sendResponse(status: true, message: Messages::SUCCESS, code: Response::HTTP_OK);
        } catch (Exception $e) {
            DB::rollBack();
            Log::channel('exception')->error($e->getMessage());

            return $this->sendResponse(status: false, message: Messages::EXCEPTION, code: Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
