<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AuthenticationCode extends Model
{
    use SoftDeletes;

    protected $table = 'authentication_codes';

    protected $primaryKey = 'id';

    protected $user_id;

    protected $authentication_code_hash;

    protected $expires_at;

    protected $code_type;

    protected $fillable = [
        'user_id',
        'authentication_code_hash',
        'expires_at',
        'code_type',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
