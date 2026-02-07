<?php

namespace Database\Seeders;

use App\Enums\AccreditationStatusEnum;
use App\Enums\AmlStatusEnum;
use App\Enums\InvestorTypeEnum;
use App\Enums\KycStatusEnum;
use App\Enums\UserStatusEnum;
use App\Enums\UserTypeEnum;
use App\Models\InvestorProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class InvestorSeeder extends Seeder
{
    public function run(): void
    {
        $investors = [
            [
                'user' => [
                    'name' => 'Alex Crombell',
                    'email' => 'investor1@test.com',
                    'phone' => '+971509876543',
                    'status' => UserStatusEnum::ACTIVE,
                ],
                'profile' => [
                    'investor_type' => InvestorTypeEnum::TIER_1,
                    'address' => 'Palm Jumeirah, Villa 42, Dubai, UAE',
                    'kyc_status' => KycStatusEnum::APPROVED,
                    'kyc_submitted_at' => now()->subDays(45),
                    'kyc_approved_at' => now()->subDays(40),
                    'aml_status' => AmlStatusEnum::CLEARED,
                    'aml_checked_at' => now()->subDays(40),
                    'accreditation_status' => AccreditationStatusEnum::VERIFIED,
                    'accreditation_expires_at' => now()->addYear(),
                    'is_active' => true,
                ],
            ],
            [
                'user' => [
                    'name' => 'Swiss Capital Partners AG',
                    'email' => 'investor2@test.com',
                    'phone' => '+41441234567',
                    'status' => UserStatusEnum::ACTIVE,
                ],
                'profile' => [
                    'investor_type' => InvestorTypeEnum::TIER_1,
                    'company_name' => 'Swiss Capital Partners AG',
                    'address' => 'Bahnhofstrasse 45, 8001 Zurich, Switzerland',
                    'kyc_status' => KycStatusEnum::APPROVED,
                    'kyc_submitted_at' => now()->subDays(60),
                    'kyc_approved_at' => now()->subDays(55),
                    'aml_status' => AmlStatusEnum::CLEARED,
                    'aml_checked_at' => now()->subDays(55),
                    'accreditation_status' => AccreditationStatusEnum::VERIFIED,
                    'accreditation_expires_at' => now()->addYears(2),
                    'is_active' => true,
                ],
            ],
            [
                'user' => [
                    'name' => 'Pacific Ventures Fund',
                    'email' => 'investor3@test.com',
                    'phone' => '+6598765432',
                    'status' => UserStatusEnum::ACTIVE,
                ],
                'profile' => [
                    'investor_type' => InvestorTypeEnum::TIER_2,
                    'company_name' => 'Pacific Ventures Fund LP',
                    'address' => 'Marina Bay Financial Centre, Tower 3, #35-01, Singapore 018982',
                    'kyc_status' => KycStatusEnum::UNDER_REVIEW,
                    'kyc_submitted_at' => now()->subDays(7),
                    'aml_status' => AmlStatusEnum::PENDING,
                    'accreditation_status' => AccreditationStatusEnum::PENDING,
                    'is_active' => true,
                ],
            ],
            [
                'user' => [
                    'name' => 'John Smith',
                    'email' => 'investor4@test.com',
                    'phone' => '+12025551234',
                    'status' => UserStatusEnum::PENDING,
                ],
                'profile' => [
                    'investor_type' => InvestorTypeEnum::TIER_3,
                    'address' => '1600 Pennsylvania Ave, Washington, DC 20500',
                    'kyc_status' => KycStatusEnum::PENDING,
                    'aml_status' => AmlStatusEnum::PENDING,
                    'accreditation_status' => AccreditationStatusEnum::PENDING,
                    'is_active' => false,
                ],
            ],
            [
                'user' => [
                    'name' => 'James Wilson',
                    'email' => 'investor5@test.com',
                    'phone' => '+442079876543',
                    'status' => UserStatusEnum::ACTIVE,
                ],
                'profile' => [
                    'investor_type' => InvestorTypeEnum::TIER_2,
                    'address' => '221B Baker Street, London NW1 6XE',
                    'kyc_status' => KycStatusEnum::REJECTED,
                    'kyc_submitted_at' => now()->subDays(20),
                    'kyc_rejection_reason' => 'Identity document provided is not clear. Please resubmit with a higher quality scan.',
                    'aml_status' => AmlStatusEnum::PENDING,
                    'accreditation_status' => AccreditationStatusEnum::PENDING,
                    'is_active' => false,
                ],
            ],
        ];

        foreach ($investors as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['user']['email']],
                array_merge($data['user'], [
                    'password' => Hash::make('password'),
                    'type' => UserTypeEnum::INVESTOR,
                    'email_verified_at' => now(),
                ])
            );

            InvestorProfile::updateOrCreate(
                ['user_id' => $user->id],
                $data['profile']
            );
        }
    }
}
