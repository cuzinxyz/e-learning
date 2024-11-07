<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Constants\Settings;
use App\Models\AuthenticationCode;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Throwable;

/**
 * Class AuthService.
 */
class AuthService
{
    protected Model $user;

    protected Model $authenticationCode;

    /**
     * @param User $user
     * @param AuthenticationCode $authenticationCode
     */
    public function __construct(
        User $user,
        AuthenticationCode $authenticationCode
    ) {
        $this->user = $user;
        $this->authenticationCode = $authenticationCode;
    }

    /**
     * @param array $data
     * @return mixed
     * @throws Throwable
     */
    public function create(array $data): mixed
    {
        $user = new $this->user($data);
        $user->saveOrFail();

        return $user;
    }

    /**
     * @param User $user
     * @param int $authCodeType
     * @return mixed
     * @throws Throwable
     */
    public function authenticationCode(User $user, int $authCodeType)
    {
        $code = generateAuthCode(Settings::AUTH_CODE_LENGTH);

        $prepareAuthCode = self::handleAuthenticationCode([
            'user_id' => $user?->id,
            'code' => $code,
            'code_type' => $authCodeType,
        ]);

        $auth = new $this->authenticationCode($prepareAuthCode);
        $auth->saveOrFail();

        return $code;
    }

    /**
     * @param array $data
     * @return array
     */
    protected static function handleAuthenticationCode(array $data): array
    {
        $expiresAt = now()->addMinutes(Settings::CODE_EXPIRES_AFTER);

        return [
            'user_id' => $data['user_id'],
            'authentication_code_hash' => hash_bcrypt($data['code']),
            'expires_at' => $expiresAt,
            'code_type' => $data['code_type'],
        ];
    }

    /**
     * @param string $email
     * @return mixed
     */
    public function findByEmail(string $email): mixed
    {
        return $this->user
            ->where('email', $email)
            ->first();
    }

    public function verifyAuthCode(User $user, string $code, int $codeType): mixed
    {
        $authCode = $this->authenticationCode
            ->whereHas('user')
            ->with('user')
            ->where('user_id', $user->id)
            ->where('code_type', $codeType)
            ->first();

        if (!$authCode || !$authCode->user) {
            return null;
        }

        $verify = password_verify($code, $authCode->authentication_code_hash);

        if (
            !$verify ||
            Carbon::parse($authCode->expires_at)->lt(now())
        ) {
            return false;
        }

        $user->email_verified_at = now();
        $user->saveOrFail();

        return $user;
    }

    public function login(array $data): User|bool
    {
        if (!Auth::attempt($data)) {
            return false;
        }

        return User::where('email', $data['email'])
            ->firstOrFail();
    }
}
