<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasswordOtp extends Model
{
    use HasFactory;

    /**
     * Prefix used in the `email` column to distinguish credential-update
     * OTPs from regular password-reset OTPs.
     */
    public const CREDENTIAL_TYPE_PREFIX = 'cred-update:';

    // Prefix stored in the "email" column to mark OTPs used for the Super Admin
    // password-change flow (kept separate from credential-update OTPs).
    public const PASSWORD_TYPE_PREFIX = 'pw-update:';

    protected $fillable = [
        'email',
        'otp',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];
}
