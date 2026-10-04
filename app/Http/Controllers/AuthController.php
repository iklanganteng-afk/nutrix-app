<?php

namespace App\Http\Controllers;

use App\Mail\OtpVerificationMail;
use App\Models\EmailOtp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Langkah 1 Registrasi: Validasi form dan kirim OTP 2 menit via email.
     */
    public function requestRegisterOtp(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'email.unique' => 'Email ini sudah terdaftar. Silakan lakukan Sign In.',
            'password.min' => 'Password minimal harus 8 karakter.',
        ]);

        if ($this->isOtpRequestRateLimited($validated['email'], 'register')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda telah mengirim kode OTP terlalu sering untuk email ini. Silakan tunggu beberapa menit lalu coba lagi.',
            ], 429);
        }

        // Generate 6 digit angka OTP
        $otpCode = sprintf('%06d', random_int(100000, 999999));

        // Hapus record OTP lama yang sudah expired atau usang untuk email ini
        EmailOtp::where('email', $validated['email'])
            ->where('action', 'register')
            ->where('expires_at', '<', now())
            ->delete();

        // Simpan ke database dengan expired tepat 2 menit (120 detik)
        $otpRecord = EmailOtp::create([
            'email' => $validated['email'],
            'otp' => EmailOtp::hashOtp($otpCode),
            'action' => 'register',
            'payload' => [
                'name' => $validated['name'],
                'password' => Hash::make($validated['password']),
            ],
            'expires_at' => now()->addMinutes(2),
            'failed_attempts' => 0,
        ]);

        // Kirim email via SMTP
        if (! $this->dispatchOtpEmail($validated['email'], $otpCode, 'register', $validated['name'])) {
            $otpRecord->delete();

            return response()->json([
                'status' => 'error',
                'message' => 'Kode OTP gagal dikirim. Periksa konfigurasi email lalu coba lagi.',
            ], 503);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Kode verifikasi 6-digit telah dikirim ke ' . $validated['email'] . '. Kode berlaku selama 2 menit.',
            'email' => $validated['email'],
            'expires_in' => 120,
        ]);
    }

    /**
     * Langkah 2 Registrasi: Verifikasi OTP 2 menit & create user baru.
     */
    public function verifyRegisterOtp(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $otpRecord = EmailOtp::where('email', $request->email)
            ->where('action', 'register')
            ->latest()
            ->first();

        if (! $otpRecord) {
            return response()->json([
                'status' => 'error',
                'message' => 'Permintaan verifikasi tidak ditemukan. Silakan kirim ulang kode.',
            ], 422);
        }

        if (! $otpRecord->isValid()) {
            return response()->json([
                'status' => 'expired',
                'message' => 'Kode verifikasi sudah kedaluwarsa (lewat dari 2 menit). Silakan klik tombol "Kirim Ulang Kode".',
            ], 422);
        }

        if ($otpRecord->failed_attempts >= 5) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kode verifikasi diblokir karena terlalu banyak percobaan gagal. Silakan minta kode baru.',
            ], 429);
        }

        if (! $otpRecord->matchesOtp($request->otp)) {
            $otpRecord->increment('failed_attempts');

            if ($otpRecord->fresh()->failed_attempts >= 5) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Kode verifikasi diblokir karena terlalu banyak percobaan gagal. Silakan minta kode baru.',
                ], 429);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Kode verifikasi salah. Harap periksa email Anda kembali.',
            ], 422);
        }

        // OTP valid, simpan user baru
        $payload = $otpRecord->payload;
        $user = User::create([
            'name' => $payload['name'] ?? 'User NUTRIX',
            'email' => $request->email,
            'password' => $payload['password'],
            'role' => 'user',
            'email_verified_at' => now(),
        ]);

        // Bersihkan OTP yang sudah digunakan
        $otpRecord->delete();

        // Login session
        Auth::login($user);
        $request->session()->regenerate();

        return response()->json([
            'status' => 'success',
            'message' => 'Registrasi berhasil! Selamat datang di NUTRIX.',
            'redirect' => route('dashboard'),
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ]);
    }

    /**
     * Langkah 1 Login: Validasi email & password, lalu kirim OTP 2 menit ke email.
     */
    public function requestLoginOtp(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['sometimes', 'boolean'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        // Cek apakah akun terdaftar dan password benar
        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => 'Akun tidak ditemukan atau password salah. Pastikan Anda telah Sign Up.',
            ]);
        }

        if ($this->isOtpRequestRateLimited($user->email, 'login')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda telah mengirim kode OTP terlalu sering untuk email ini. Silakan tunggu beberapa menit lalu coba lagi.',
            ], 429);
        }

        // Generate 6 digit angka OTP
        $otpCode = sprintf('%06d', random_int(100000, 999999));

        // Hapus record OTP lama yang sudah expired atau usang untuk email ini
        EmailOtp::where('email', $user->email)
            ->where('action', 'login')
            ->where('expires_at', '<', now())
            ->delete();

        // Simpan ke database dengan expired tepat 2 menit
        $otpRecord = EmailOtp::create([
            'email' => $user->email,
            'otp' => EmailOtp::hashOtp($otpCode),
            'action' => 'login',
            'expires_at' => now()->addMinutes(2),
            'failed_attempts' => 0,
        ]);

        // Kirim email via SMTP
        if (! $this->dispatchOtpEmail($user->email, $otpCode, 'login', $user->name)) {
            $otpRecord->delete();

            return response()->json([
                'status' => 'error',
                'message' => 'Kode OTP gagal dikirim. Periksa konfigurasi email lalu coba lagi.',
            ], 503);
        }

        $request->session()->put('auth.remember', $request->boolean('remember'));

        return response()->json([
            'status' => 'success',
            'message' => 'Kode verifikasi login 6-digit telah dikirim ke ' . $user->email . '. Berlaku selama 2 menit.',
            'email' => $user->email,
            'role' => $user->role,
            'expires_in' => 120,
        ]);
    }

    /**
     * Langkah 2 Login: Verifikasi OTP 2 menit & alihkan sesuai role (Admin vs User).
     */
    public function verifyLoginOtp(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $otpRecord = EmailOtp::where('email', $request->email)
            ->where('action', 'login')
            ->latest()
            ->first();

        if (! $otpRecord) {
            return response()->json([
                'status' => 'error',
                'message' => 'Permintaan verifikasi login tidak ditemukan. Harap login kembali.',
            ], 422);
        }

        if (! $otpRecord->isValid()) {
            return response()->json([
                'status' => 'expired',
                'message' => 'Kode verifikasi login telah kedaluwarsa (lewat dari 2 menit). Silakan kirim ulang kode.',
            ], 422);
        }

        if ($otpRecord->failed_attempts >= 5) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kode verifikasi diblokir karena terlalu banyak percobaan gagal. Silakan minta kode baru.',
            ], 429);
        }

        if (! $otpRecord->matchesOtp($request->otp)) {
            $otpRecord->increment('failed_attempts');

            if ($otpRecord->fresh()->failed_attempts >= 5) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Kode verifikasi diblokir karena terlalu banyak percobaan gagal. Silakan minta kode baru.',
                ], 429);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Kode OTP verifikasi salah. Silakan periksa kotak masuk email Anda.',
            ], 422);
        }

        $user = User::where('email', $request->email)->firstOrFail();

        // Bersihkan OTP yang sudah digunakan
        $otpRecord->delete();

        // Carry the remember choice across the two-step login flow.
        $remember = (bool) $request->session()->pull('auth.remember', false);
        Auth::login($user, $remember);
        $request->session()->regenerate();

        // Alur pengalihan berdasarkan Role
        $redirectUrl = $user->isAdmin() ? route('admin.dashboard') : route('dashboard');

        return response()->json([
            'status' => 'success',
            'message' => $user->isAdmin() 
                ? 'Autentikasi Master Admin Berhasil. Mengarahkan ke Master Command Center...' 
                : 'Verifikasi sukses! Selamat datang kembali.',
            'redirect' => $redirectUrl,
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ]);
    }

    /**
     * Kirim Ulang OTP jika 2 menit habis.
     */
    public function resendOtp(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'action' => ['required', 'in:login,register'],
        ]);

        if ($this->isOtpRequestRateLimited($request->email, $request->action)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda telah mengirim kode OTP terlalu sering untuk email ini. Silakan tunggu beberapa menit lalu coba lagi.',
            ], 429);
        }

        $existing = EmailOtp::where('email', $request->email)
            ->where('action', $request->action)
            ->latest()
            ->first();

        if (! $existing) {
            return response()->json([
                'status' => 'error',
                'message' => 'Sesi tidak ditemukan. Silakan isi form kembali.',
            ], 422);
        }

        $otpCode = sprintf('%06d', random_int(100000, 999999));

        $existing->update([
            'otp' => EmailOtp::hashOtp($otpCode),
            'expires_at' => now()->addMinutes(2),
            'failed_attempts' => 0,
        ]);

        $recipientName = 'User';
        if ($request->action === 'register') {
            $recipientName = $existing->payload['name'] ?? 'User';
        } else {
            $user = User::where('email', $request->email)->first();
            if ($user) $recipientName = $user->name;
        }

        if (! $this->dispatchOtpEmail($request->email, $otpCode, $request->action, $recipientName)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kode OTP gagal dikirim. Periksa konfigurasi email lalu coba lagi.',
            ], 503);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Kode OTP baru berhasil dikirim ke ' . $request->email . '. Berlaku selama 2 menit.',
            'expires_in' => 120,
        ]);
    }

    /**
     * Logout pengguna.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Logged out', 'redirect' => route('welcome')]);
        }

        return redirect()->route('welcome');
    }

    /**
     * Helper pengiriman email via Gmail REST API (HTTPS Port 443) dengan fallback SMTP.
     */
    private function dispatchOtpEmail(string $email, string $otp, string $action, string $name): bool
    {
        // 0. Saat automated test berjalan, selalu gunakan Mail Facade agar Mail::fake() berfungsi
        if (app()->runningUnitTests()) {
            try {
                Mail::to($email)->send(new OtpVerificationMail($otp, $action, $name));
                return true;
            } catch (\Throwable $e) {
                return false;
            }
        }

        // 1. Coba kirim via Gmail REST API (Port 443 HTTPS - Bebas blokir Railway / Cloud)
        $gmailApi = app(\App\Services\GmailApiService::class);
        if ($gmailApi->isConfigured()) {
            try {
                $subject = $action === 'register' 
                    ? '🔐 [NUTRIX] Kode Verifikasi Pendaftaran Akun' 
                    : '🔐 [NUTRIX] Kode Verifikasi Masuk (Login)';

                $htmlBody = view('emails.otp', [
                    'otp' => $otp,
                    'action' => $action,
                    'name' => $name,
                ])->render();

                if ($gmailApi->sendRawEmail($email, $subject, $htmlBody)) {
                    return true;
                }
                Log::warning("Pengiriman via Gmail API gagal, mencoba fallback ke SMTP standard...");
            } catch (\Throwable $e) {
                Log::warning("Exception pada Gmail API ({$e->getMessage()}), mencoba fallback ke SMTP standard...");
            }
        }

        // 2. Fallback: Kirim via Mail Facade (SMTP / Local Mailer)
        try {
            Mail::to($email)->send(new OtpVerificationMail($otp, $action, $name));
            return true;
        } catch (\Throwable $e) {
            // Jangan pernah menulis kode OTP ke log aplikasi.
            Log::error('Gagal mengirim email OTP SMTP: ' . $e->getMessage(), [
                'email' => $email,
                'action' => $action,
            ]);
            return false;
        }
    }

    /**
     * Batasi pengiriman OTP berdasarkan email, bukan IP.
     */
    private function isOtpRequestRateLimited(string $email, string $action): bool
    {
        $recentCount = EmailOtp::where('email', $email)
            ->where('action', $action)
            ->where('created_at', '>=', now()->subMinutes(2))
            ->count();

        return $recentCount >= 3;
    }

    /**
     * Generate alamat kosmetik ala Web3, contoh: 0xA1B2...C3
     */
}
