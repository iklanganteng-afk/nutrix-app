<?php

namespace Tests\Feature;

use App\Models\EmailOtp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AuthOtpTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_requires_password_confirmation_and_sends_an_otp(): void
    {
        Mail::fake();

        $this->postJson('/auth/register/request-otp', [
            'name' => 'New Farmer',
            'email' => 'farmer@example.test',
            'password' => 'password123',
            'password_confirmation' => 'different-password',
        ])->assertUnprocessable()->assertJsonValidationErrors('password');

        $response = $this->postJson('/auth/register/request-otp', [
            'name' => 'New Farmer',
            'email' => 'farmer@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertOk()->assertJsonPath('status', 'success');
        $this->assertDatabaseHas('email_otps', [
            'email' => 'farmer@example.test',
            'action' => 'register',
        ]);
        $this->assertTrue(EmailOtp::firstOrFail()->expires_at->between(now()->addMinute(), now()->addMinutes(3)));
    }
    public function test_login_request_otp_for_registered_admin()
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@nutrix.io'],
            [
                'name' => 'NUTRIX System Overseer',
                'password' => bcrypt('NutrixAdmin#2026'),
                'role' => 'admin',
            ]
        );

        $response = $this->postJson('/auth/login/request-otp', [
            'email' => 'admin@nutrix.io',
            'password' => 'NutrixAdmin#2026',
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'status' => 'success',
            'role' => 'admin',
        ]);

        $this->assertDatabaseHas('email_otps', [
            'email' => 'admin@nutrix.io',
            'action' => 'login',
        ]);
    }

    public function test_verify_login_otp_redirects_admin_to_admin_dashboard()
    {
        User::create([
            'name' => 'Admin Nutrix',
            'email' => 'admin@nutrix.io',
            'password' => bcrypt('NutrixAdmin#2026'),
            'role' => 'admin',
        ]);

        $otp = EmailOtp::create([
            'email' => 'admin@nutrix.io',
            'otp' => '654321',
            'action' => 'login',
            'expires_at' => now()->addMinutes(2),
        ]);

        $response = $this->postJson('/auth/login/verify-otp', [
            'email' => 'admin@nutrix.io',
            'otp' => '654321',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'redirect' => route('admin.dashboard'),
        ]);

        $this->assertAuthenticated();
    }

    public function test_login_remember_choice_survives_otp_and_sets_a_remember_token(): void
    {
        $user = User::create([
            'name' => 'Remembered Farmer',
            'email' => 'remembered@example.test',
            'password' => bcrypt('password123'),
            'role' => 'user',
        ]);
        EmailOtp::create([
            'email' => $user->email,
            'otp' => Hash::make('654321'),
            'action' => 'login',
            'expires_at' => now()->addMinutes(2),
        ]);

        $this->withSession(['auth.remember' => true])
            ->postJson('/auth/login/verify-otp', [
                'email' => $user->email,
                'otp' => '654321',
            ])
            ->assertOk();

        $this->assertNotEmpty($user->fresh()->getRememberToken());
        $this->assertAuthenticatedAs($user);
    }

    public function test_expired_otp_is_rejected()
    {
        EmailOtp::create([
            'email' => 'admin@nutrix.io',
            'otp' => '112233',
            'action' => 'login',
            'expires_at' => now()->subMinute(), // telah lewat 2 menit
        ]);

        $response = $this->postJson('/auth/login/verify-otp', [
            'email' => 'admin@nutrix.io',
            'otp' => '112233',
        ]);

        $response->assertStatus(422);
        $response->assertJsonFragment([
            'status' => 'expired',
        ]);
    }

    public function test_registration_stores_hashed_otp_in_database()
    {
        Mail::fake();

        $response = $this->postJson('/auth/register/request-otp', [
            'name' => 'Farmer Hash Test',
            'email' => 'hash-test@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertOk();

        $otpRecord = EmailOtp::firstOrFail();
        Mail::assertSent(\App\Mail\OtpVerificationMail::class, function ($mail) use ($otpRecord) {
            $this->assertNotSame($mail->otp, $otpRecord->otp);
            $this->assertTrue(Hash::check($mail->otp, $otpRecord->otp));

            return true;
        });
    }

    public function test_otp_request_is_rate_limited_per_email()
    {
        Mail::fake();

        $payload = [
            'name' => 'Farmer Rate Limit',
            'email' => 'rate-limit@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        foreach (range(1, 4) as $attempt) {
            $response = $this->postJson('/auth/register/request-otp', $payload);

            if ($attempt < 4) {
                $response->assertOk();
                continue;
            }

            $response->assertStatus(429)
                ->assertJsonFragment(['status' => 'error']);
        }
    }

    public function test_otp_verification_is_locked_after_five_failed_attempts()
    {
        $user = User::create([
            'name' => 'Locked Farmer',
            'email' => 'locked@example.test',
            'password' => bcrypt('password123'),
            'role' => 'user',
        ]);

        $otp = EmailOtp::create([
            'email' => $user->email,
            'otp' => '123456',
            'action' => 'login',
            'expires_at' => now()->addMinutes(2),
            'failed_attempts' => 4,
        ]);

        $response = $this->postJson('/auth/login/verify-otp', [
            'email' => $user->email,
            'otp' => '999999',
        ]);

        $response->assertStatus(429)
            ->assertJsonFragment(['status' => 'error']);

        $this->assertDatabaseHas('email_otps', [
            'email' => $user->email,
            'action' => 'login',
        ]);

        $this->assertSame(5, $otp->fresh()->failed_attempts);
    }

    public function test_regular_user_cannot_access_admin_dashboard()
    {
        $user = User::firstOrCreate(
            ['email' => 'regular@nutrix.io'],
            [
                'name' => 'Regular Farmer',
                'password' => bcrypt('password123'),
                'role' => 'user',
            ]
        );

        $response = $this->actingAs($user)->get('/admin');
        $response->assertRedirect(route('dashboard'));
    }
}
