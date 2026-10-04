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
     * Hash OTP dengan HMAC-SHA256 untuk performa instan (<0.1ms) dan keamanan tinggi.
     */
    public static function hashOtp(string $otp): string
    {
        return hash_hmac('sha256', $otp, (string) config('app.key', 'nutrix_secret_otp_fallback'));
    }

    /**
     * Memeriksa apakah OTP masih berlaku (belum melewati batas 2 menit).
     */
    public function isValid(): bool
    {
        return $this->expires_at->isFuture();
    }

    /**
     * Verifikasi OTP baik yang di-hash dengan HMAC-SHA256, bcrypt, maupun legacy plaintext.
     */
    public function matchesOtp(string $otp): bool
    {
        if (empty($this->otp)) {
            return false;
        }

        // 1. Dukungan bcrypt / Argon2 (legacy)
        if (Hash::isHashed($this->otp)) {
            return Hash::check($otp, $this->otp);
        }

        // 2. Dukungan HMAC-SHA256 (64 karakter heksadesimal, super cepat)
        if (strlen($this->otp) === 64 && ctype_xdigit($this->otp)) {
            $expected = self::hashOtp($otp);
            return hash_equals($this->otp, $expected);
        }

        // 3. Fallback plaintext (misal data test legacy)
        return hash_equals((string) $this->otp, (string) $otp);
    }
}
