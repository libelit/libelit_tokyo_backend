<?php

namespace Database\Seeders;

use App\Enums\UserStatusEnum;
use App\Enums\UserTypeEnum;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@libelit.com'],
            [
                'name' => 'System Administrator',
                'email' => 'admin@libelit.com',
                'password' => Hash::make('password'),
                'type' => UserTypeEnum::ADMIN,
                'status' => UserStatusEnum::ACTIVE,
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'superadmin@libelit.com'],
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@libelit.com',
                'password' => Hash::make('password'),
                'type' => UserTypeEnum::ADMIN,
                'status' => UserStatusEnum::ACTIVE,
                'email_verified_at' => now(),
            ]
        );
    }
}
