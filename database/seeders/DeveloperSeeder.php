<?php

namespace Database\Seeders;

use App\Enums\KybStatusEnum;
use App\Enums\UserStatusEnum;
use App\Enums\UserTypeEnum;
use App\Models\DeveloperProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DeveloperSeeder extends Seeder
{
    public function run(): void
    {
        $developers = [
            [
                'user' => [
                    'name' => 'Emirates Properties LLC',
                    'email' => 'owner1@test.com',
                    'phone' => '+971501234567',
                    'status' => UserStatusEnum::ACTIVE,
                ],
                'profile' => [
                    'company_name' => 'Emirates Properties LLC',
                    'company_registration_number' => 'LLC-2024-001234',
                    'address' => 'Business Bay, Tower A, Floor 15, Dubai, UAE',
                    'kyb_status' => KybStatusEnum::APPROVED,
                    'kyb_submitted_at' => now()->subDays(30),
                    'kyb_approved_at' => now()->subDays(25),
                ],
            ],
            [
                'user' => [
                    'name' => 'Manhattan Real Estate Corp',
                    'email' => 'owner2@test.com',
                    'phone' => '+12125551234',
                    'status' => UserStatusEnum::ACTIVE,
                ],
                'profile' => [
                    'company_name' => 'Manhattan Real Estate Corp',
                    'company_registration_number' => 'NY-CORP-2023-5678',
                    'address' => '350 Fifth Avenue, Suite 2000, New York, NY 10118',
                    'kyb_status' => KybStatusEnum::UNDER_REVIEW,
                    'kyb_submitted_at' => now()->subDays(5),
                ],
            ],
            [
                'user' => [
                    'name' => 'London Property Holdings',
                    'email' => 'owner3@test.com',
                    'phone' => '+442071234567',
                    'status' => UserStatusEnum::PENDING,
                ],
                'profile' => [
                    'company_name' => 'London Property Holdings Ltd',
                    'company_registration_number' => 'UK-12345678',
                    'address' => '1 Canada Square, Canary Wharf, London E14 5AB',
                    'kyb_status' => KybStatusEnum::PENDING,
                ],
            ],
            [
                'user' => [
                    'name' => 'Singapore Developments Pte',
                    'email' => 'owner4@test.com',
                    'phone' => '+6561234567',
                    'status' => UserStatusEnum::ACTIVE,
                ],
                'profile' => [
                    'company_name' => 'Singapore Developments Pte Ltd',
                    'company_registration_number' => 'SG-202401234A',
                    'address' => '1 Raffles Place, Tower 1, #40-01, Singapore 048616',
                    'kyb_status' => KybStatusEnum::REJECTED,
                    'kyb_submitted_at' => now()->subDays(15),
                    'kyb_rejection_reason' => 'Company registration documents are expired. Please provide updated documentation.',
                ],
            ],
        ];

        foreach ($developers as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['user']['email']],
                array_merge($data['user'], [
                    'password' => Hash::make('password'),
                    'type' => UserTypeEnum::DEVELOPER,
                    'email_verified_at' => now(),
                ])
            );

            DeveloperProfile::updateOrCreate(
                ['user_id' => $user->id],
                $data['profile']
            );
        }
    }
}
