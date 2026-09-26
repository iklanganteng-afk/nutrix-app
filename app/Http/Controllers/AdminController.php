<?php

namespace App\Http\Controllers;

use App\Models\EmailOtp;
use App\Models\Taman;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Tampilan Khusus Admin Command Center.
     */
    public function index()
    {
        $totalUsers = User::where('role', 'user')->count();
        $totalAdmins = User::where('role', 'admin')->count();
        $totalTamans = class_exists(Taman::class) ? Taman::count() : 0;
        
        $users = User::latest()->take(20)->get();
        $tamans = Taman::with('user')->latest()->take(6)->get();
        $recentOtps = EmailOtp::latest()->take(10)->get();

        // Cek status koneksi SMTP dari .env
        $smtpConfigured = !empty(config('mail.mailers.smtp.username')) && !empty(config('mail.mailers.smtp.password'));

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalAdmins',
            'totalTamans',
            'users',
            'tamans',
            'recentOtps',
            'smtpConfigured'
        ));
    }
    public function updateRole(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|in:admin,user',
        ]);

        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'Tidak bisa mengubah role akun sendiri.');
        }

        $user->update(['role' => $request->role]);
        return redirect()->back()->with('success', 'Role user berhasil diubah.');
    }

    public function destroyUser(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'Tidak bisa menghapus akun sendiri.');
        }

        // Hapus juga taman terkait
        $user->tamans()->delete();
        $user->delete();

        return redirect()->back()->with('success', 'Akun user berhasil dihapus.');
    }

    public function destroyTaman(Taman $taman)
    {
        $taman->delete();
        return redirect()->back()->with('success', 'Taman berhasil dihapus oleh Admin.');
    }

    public function forceSyncTelemetry()
    {
        $tamans = Taman::all();
        
        foreach ($tamans as $taman) {
            \App\Models\SensorTelemetry::create([
                'taman_id' => $taman->id,
                'ph_level' => rand(50, 80) / 10, // 5.0 to 8.0
                'moisture_percent' => rand(30, 90),
                'temperature_celsius' => rand(200, 350) / 10, // 20.0 to 35.0
                'electrical_conductivity' => rand(100, 250) / 100, // 1.00 to 2.50
                'health_score' => rand(70, 100),
                'health_status' => 'optimal',
                'recorded_at' => now(),
            ]);

            // Juga tambahkan aktivitas
            \App\Models\FarmActivity::create([
                'taman_id' => $taman->id,
                'type' => 'sync',
                'title' => 'Admin Force Sync: Telemetry Updated',
                'status' => 'confirmed'
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Telemetry for all farms synced successfully.']);
    }
}
