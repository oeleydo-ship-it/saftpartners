<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Safe to run on every deployment: it only creates records that are missing,
 * so content edited in the admin CMS and existing passwords are never overwritten.
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->seedAdmin();
        $this->call(ContentSeeder::class);
    }

    private function seedAdmin(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@safpartners.ae');
        $password = env('ADMIN_PASSWORD');

        if (User::where('email', $email)->exists()) return;

        if (blank($password)) {
            if (app()->isProduction()) {
                $this->command?->warn('ADMIN_PASSWORD is not set: skipping admin account creation (use the /setup wizard).');
                return;
            }
            $password = 'ChangeMe123!';
        }

        User::create([
            'name' => 'SAF Partners Administrator',
            'email' => $email,
            'password' => $password,
        ])->forceFill(['role' => 'super_admin', 'email_verified_at' => now()])->save();
    }
}
