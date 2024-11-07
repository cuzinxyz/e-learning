<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Constants\AuthenticationCode;
use App\Constants\Messages;
use App\Events\Auth\UserCreated;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\VerifyAccountRequest;
use App\Services\Auth\AuthService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    protected AuthService $authService;

    private const USER_NOT_FOUND = 0;

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

    /**
     * @param VerifyAccountRequest $req
     * @return JsonResponse
     */
    public function verifyAccount(VerifyAccountRequest $req): JsonResponse
    {
        try {
            DB::beginTransaction();

            ['email' => $email, 'code' => $code] = $req->validated();

            $user = $this->checkValidUser($email);

            if ($user === self::USER_NOT_FOUND) {
                return $this->sendResponse(status: false, message: Messages::NOT_FOUND, code: Response::HTTP_NOT_FOUND);
            }

            if ($user && $user?->email_verified_at) {
                return $this->sendResponse(status: false, message: Messages::AUTHENTICATED, code: Response::HTTP_FORBIDDEN);
            }

            $verifyCode = $this->authService->verifyAuthCode(user: $user, code: $code, codeType: AuthenticationCode::REGISTER);

            DB::commit();

            if (!$verifyCode) {
                // when code is invalid or has expired
                return $this->sendResponse(status: false, message: Messages::AUTHENTICATION_CODE_NOT_VALID, code: Response::HTTP_NOT_FOUND);
            }

            return $this->sendResponse(status: true, message: Messages::SUCCESS, code: Response::HTTP_OK);
        } catch (Exception $e) {
            DB::rollBack();
            Log::channel('exception')->error($e->getMessage());

            return $this->sendResponse(status: false, message: Messages::EXCEPTION, code: Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param string $email
     * @return mixed
     */
    protected function checkValidUser(string $email): mixed
    {
        $user = $this->authService->findByEmail($email);

        if (!$user) {
            return self::USER_NOT_FOUND;
        }

        return $user;
    }

    /**
     * @param LoginRequest $req
     * @return JsonResponse
     */
    public function login(LoginRequest $req): JsonResponse
    {
        try {
            $user = $this->authService->login($req->validated());

            if (!$user) {
                return $this->sendResponse(status: false, message: Messages::WRONG_INFORMATION, code: Response::HTTP_UNAUTHORIZED);
            }

            if (!$user?->email_verified_at) {
                return $this->sendResponse(status: false, message: Messages::NOT_AUTHENTICATED, code: Response::HTTP_UNAUTHORIZED);
            }

            $response = [
                'access_token' => $user?->createToken($req->device_name ?: '')?->plainTextToken
            ];

            return $this->sendResponse(status: true, message: Messages::SUCCESS, code: Response::HTTP_OK, data: $response);

        } catch (Exception $e) {
            DB::rollBack();
            Log::channel('exception')->error($e->getMessage());

            return $this->sendResponse(status: false, message: Messages::EXCEPTION, code: Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
