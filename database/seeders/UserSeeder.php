<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@qrmh.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        // Create test users for different roles
        User::create([
            'name' => 'Test Manager',
            'email' => 'manager@qrmh.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Scanner User',
            'email' => 'scanner@qrmh.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $this->command->info('✅ Created 3 test users:');
        $this->command->info('   - admin@qrmh.com / password (Admin)');
        $this->command->info('   - manager@qrmh.com / password (Manager)');
        $this->command->info('   - scanner@qrmh.com / password (Scanner)');
    }
}
