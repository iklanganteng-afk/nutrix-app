<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin is opt-in and configured locally through .env; never ship a
        // usable production password in source control.
        $adminEmail = env('NUTRIX_ADMIN_EMAIL');
        $adminPassword = env('NUTRIX_ADMIN_PASSWORD');

        if ($adminEmail && $adminPassword) {
            $admin = User::firstOrNew(['email' => $adminEmail]);
            $admin->fill([
                'name' => env('NUTRIX_ADMIN_NAME', 'NUTRIX Administrator'),
                'password' => bcrypt($adminPassword),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]);

            $admin->save();
        }

        // Regular User Demo
        User::updateOrCreate(
            ['email' => 'user@nutrix.io'],
            [
                'name' => 'Demo Agronomist',
                'password' => bcrypt('NutrixUser#2026'),
                'role' => 'user',
                'email_verified_at' => now(),
            ]
        );
    }
}
