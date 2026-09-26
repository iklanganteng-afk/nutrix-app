<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kode Verifikasi NUTRIX</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #0d1217;
            color: #e2e8f0;
            margin: 0;
            padding: 24px;
        }
        .container {
            max-width: 520px;
            margin: 0 auto;
            background: #151d24;
            border: 1px solid rgba(16, 185, 129, 0.25);
            border-radius: 16px;
            padding: 32px 28px;
            box-shadow: 0 12px 36px rgba(0, 0, 0, 0.5);
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.4rem;
            font-weight: 800;
            color: #10b981;
            letter-spacing: 1px;
            margin-bottom: 24px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            padding-bottom: 16px;
        }
        .greeting {
            font-size: 1rem;
            color: #94a3b8;
            margin-bottom: 12px;
        }
        .instruction {
            font-size: 0.95rem;
            line-height: 1.5;
            color: #cbd5e1;
            margin-bottom: 24px;
        }
        .otp-box {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.12), rgba(6, 78, 59, 0.25));
            border: 2px dashed #10b981;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            margin-bottom: 24px;
        }
        .otp-code {
            font-family: 'Courier New', Courier, monospace;
            font-size: 2.5rem;
            font-weight: 900;
            letter-spacing: 10px;
            color: #34d399;
            margin: 0;
        }
        .timer-warning {
            display: inline-block;
            background: rgba(239, 68, 68, 0.15);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 20px;
            padding: 6px 14px;
            font-size: 0.82rem;
            font-weight: 600;
            margin-top: 10px;
        }
        .footer {
            font-size: 0.8rem;
            color: #64748b;
            border-top: 1px solid rgba(255, 255, 255, 0.06);
            padding-top: 18px;
            text-align: center;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="brand">
            🌱 NUTRIX AGRI-TECH
        </div>
        <div class="greeting">
            Halo <strong>{{ $name }}</strong>,
        </div>
        <div class="instruction">
            @if($action === 'register')
                Terima kasih telah bergabung dengan platform ekosistem agrikultur cerdas NUTRIX. Silakan gunakan kode verifikasi di bawah ini untuk mengaktifkan akun Anda:
            @else
                Kami menerima permintaan login ke akun NUTRIX Anda. Masukkan kode verifikasi satu kali (OTP) berikut untuk melanjutkan:
            @endif
        </div>

        <div class="otp-box">
            <div class="otp-code">{{ $otp }}</div>
            <div class="timer-warning">
                ⏱️ Kode ini hanya berlaku selama <strong>2 Menit</strong>
            </div>
        </div>

        <p style="font-size: 0.88rem; color: #94a3b8; line-height: 1.5;">
            Jangan berikan kode ini kepada siapa pun termasuk staf NUTRIX. Jika Anda tidak merasa melakukan permintaan ini, abaikan email ini secara aman.
        </p>

        <div class="footer">
            NUTRIX Decentralized Agriculture Network &copy; {{ date('Y') }}<br>
            Sistem Keamanan Terintegrasi Otentikasi OTP
        </div>
    </div>
</body>
</html>
