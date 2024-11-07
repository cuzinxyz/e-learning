<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AuthenticationCode extends Model
{
    use SoftDeletes;

    protected $table = 'authentication_codes';

    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'authentication_code_hash',
        'expires_at',
        'code_type',
    ];
}
