<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TamanController;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

// Welcome page: public product story, hero, architecture, and dashboard preview.
Route::get('/', function () {
    return view('welcome');
});

Route::get('/welcome', function () {
    return view('welcome');
})->name('welcome');

// Redirect /landingpage ke halaman utama
Route::get('/landingpage', function () {
    return redirect()->route('welcome');
})->name('landingpage');

// Fitur Mascot Studio Playground & Konten Maker
Route::get('/mascot-studio', function () {
    return view('mascot-studio');
})->name('mascot.studio');

// Halaman Kloningan Welcome Khusus Konten Video (dengan panel kontrol kiri & crop boundary)
Route::get('/demo', function () {
    return view('welcome-demo');
})->name('welcome.demo');

Route::get('/welcome-demo', function () {
    return view('welcome-demo');
});

Route::get('/locales/{locale}', function (string $locale) {
    abort_unless(in_array($locale, ['id', 'en-GB', 'en-US', 'en-CA', 'jv', 'ja', 'ar', 'ms'], true), 404);

    $path = base_path("lang/{$locale}.json");
    abort_unless(File::exists($path), 404);

    return response()->json(File::json($path), headers: [
        'Cache-Control' => 'public, max-age=300',
    ]);
})->name('locales.show');

// Auth 2-Step OTP Endpoints
Route::prefix('auth')->name('auth.')->group(function () {
    Route::post('/register/request-otp', [AuthController::class, 'requestRegisterOtp'])->name('register.request_otp');
    Route::post('/register/verify-otp', [AuthController::class, 'verifyRegisterOtp'])->name('register.verify_otp');
    
    Route::post('/login/request-otp', [AuthController::class, 'requestLoginOtp'])->name('login.request_otp');
    Route::post('/login/verify-otp', [AuthController::class, 'verifyLoginOtp'])->name('login.verify_otp');
    
    Route::post('/resend-otp', [AuthController::class, 'resendOtp'])->name('resend_otp');
});

// Route legacy support
Route::post('/register', [AuthController::class, 'requestRegisterOtp'])->name('register.submit');
Route::post('/login', [AuthController::class, 'requestLoginOtp'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Fallback GET /login — arahkan ke welcome (bukan dashboard, karena dashboard butuh auth)
Route::get('/login', function () {
    return redirect()->route('welcome');
})->name('login');

// RUTE KHUSUS ADMIN (TAMPILAN BERBEDA DENGAN USER BIASA)
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('dashboard');
});

// Aksi yang benar-benar butuh login
Route::middleware('auth')->group(function () {
    // Dashboard Pengguna Biasa
    Route::get('/dashboard', [TamanController::class, 'index'])->name('dashboard');

    Route::post('/taman', [TamanController::class, 'store'])->name('taman.store');
    Route::get('/taman/{taman}', [TamanController::class, 'show'])->name('taman.show');
    Route::patch('/taman/{taman}', [TamanController::class, 'update'])->name('taman.update');
    Route::delete('/taman/{taman}', [TamanController::class, 'destroy'])->name('taman.destroy');
});