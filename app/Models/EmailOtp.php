<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class EmailOtp extends Model
{
    protected $fillable = [
        'email',
        'otp',
        'action',
        'payload',
        'expires_at',
        'failed_attempts',
    ];

    protected $casts = [
        'payload' => 'array',
        'expires_at' => 'datetime',
        'failed_attempts' => 'integer',
    ];

    /**
     * Memeriksa apakah OTP masih berlaku (belum melewati batas 2 menit).
     */
    public function isValid(): bool
    {
        return $this->expires_at->isFuture();
    }

    /**
     * Verifikasi OTP baik yang sudah di-hash maupun legacy plaintext.
     */
    public function matchesOtp(string $otp): bool
    {
        if (empty($this->otp)) {
            return false;
        }

        if (Hash::isHashed($this->otp)) {
            return Hash::check($otp, $this->otp);
        }

        return hash_equals((string) $this->otp, (string) $otp);
    }
}
