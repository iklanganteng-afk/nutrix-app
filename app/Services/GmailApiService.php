<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GmailApiService
{
    protected string $clientId;
    protected string $clientSecret;
    protected string $refreshToken;
    protected string $senderEmail;
    protected string $senderName;

    public function __construct()
    {
        $this->clientId = (string) config('services.google.client_id', env('GOOGLE_CLIENT_ID', ''));
        $this->clientSecret = (string) config('services.google.client_secret', env('GOOGLE_CLIENT_SECRET', ''));
        $this->refreshToken = (string) config('services.google.refresh_token', env('GOOGLE_REFRESH_TOKEN', ''));
        $this->senderEmail = (string) config('mail.from.address', env('MAIL_FROM_ADDRESS', 'iklanganteng@gmail.com'));
        $this->senderName = (string) config('mail.from.name', env('MAIL_FROM_NAME', 'NUTRIX Cyber-Agritech'));
    }

    /**
     * Cek apakah kredensial Gmail API lengkap.
     */
    public function isConfigured(): bool
    {
        return !empty($this->clientId) && !empty($this->clientSecret) && !empty($this->refreshToken);
    }

    /**
     * Dapatkan access token baru menggunakan refresh token via HTTPS (Port 443).
     */
    public function getAccessToken(): ?string
    {
        try {
            $response = Http::asForm()->timeout(10)->post('https://oauth2.googleapis.com/token', [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'refresh_token' => $this->refreshToken,
                'grant_type' => 'refresh_token',
            ]);

            if ($response->successful()) {
                return $response->json('access_token');
            }

            Log::error('Gmail API Token Refresh Failed: ' . $response->body());
            return null;
        } catch (\Throwable $e) {
            Log::error('Gmail API Token Refresh Exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Kirim email menggunakan Gmail REST API via HTTPS (Port 443).
     * 100% lolos blokir firewall Railway / Cloud Hosting.
     */
    public function sendRawEmail(string $toEmail, string $subject, string $htmlBody): bool
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return false;
        }

        try {
            // Susun format RFC 2822 MIME Message
            $boundary = uniqid('np', true);
            $rawMessage  = "From: =?UTF-8?B?" . base64_encode($this->senderName) . "?= <{$this->senderEmail}>\r\n";
            $rawMessage .= "To: <{$toEmail}>\r\n";
            $rawMessage .= "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n";
            $rawMessage .= "MIME-Version: 1.0\r\n";
            $rawMessage .= "Content-Type: multipart/alternative; boundary=\"{$boundary}\"\r\n\r\n";

            // Plain text part
            $rawMessage .= "--{$boundary}\r\n";
            $rawMessage .= "Content-Type: text/plain; charset=UTF-8\r\n";
            $rawMessage .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
            $rawMessage .= strip_tags($htmlBody) . "\r\n\r\n";

            // HTML part
            $rawMessage .= "--{$boundary}\r\n";
            $rawMessage .= "Content-Type: text/html; charset=UTF-8\r\n";
            $rawMessage .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
            $rawMessage .= $htmlBody . "\r\n\r\n";
            $rawMessage .= "--{$boundary}--\r\n";

            // URL-safe Base64 encode
            $encodedMessage = rtrim(strtr(base64_encode($rawMessage), '+/', '-_'), '=');

            $sendResponse = Http::withToken($accessToken)
                ->timeout(10)
                ->post('https://gmail.googleapis.com/gmail/v1/users/me/messages/send', [
                    'raw' => $encodedMessage,
                ]);

            if ($sendResponse->successful()) {
                Log::info("Email OTP berhasil dikirim via Gmail API (HTTPS) ke: {$toEmail}");
                return true;
            }

            Log::error('Gmail API Send Failed: ' . $sendResponse->body());
            return false;
        } catch (\Throwable $e) {
            Log::error('Gmail API Send Exception: ' . $e->getMessage());
            return false;
        }
    }
}
