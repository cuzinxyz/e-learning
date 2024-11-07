<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Constants\Settings;
use App\Models\AuthenticationCode;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
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
    protected static function handleAuthenticationCode(array $data)
    {
        $expiresAt = now()->addMinutes(Settings::CODE_EXPIRES_AFTER);

        return [
            'user_id' => $data['user_id'],
            'authentication_code_hash' => hash_bcrypt($data['code']),
            'expires_at' => $expiresAt,
            'code_type' => $data['code_type'],
        ];
    }
}
