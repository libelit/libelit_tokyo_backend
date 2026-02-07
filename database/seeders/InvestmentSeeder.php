<?php

namespace Database\Seeders;

use App\Enums\InvestmentStatusEnum;
use App\Enums\PaymentMethodEnum;
use App\Enums\ProjectStatusEnum;
use App\Models\Investment;
use App\Models\InvestorProfile;
use App\Models\Project;
use Illuminate\Database\Seeder;
use Ramsey\Uuid\Uuid;

class InvestmentSeeder extends Seeder
{
    public function run(): void
    {
        $submittedProject = Project::where('status', ProjectStatusEnum::SUBMITTED)->first();
        $fundingProject = Project::where('status', ProjectStatusEnum::FUNDING)->first();

        $approvedInvestors = InvestorProfile::whereHas('user', function ($query) {
            $query->whereIn('email', ['investor1@test.com', 'investor2@test.com']);
        })->get();

        if (!$submittedProject || $approvedInvestors->isEmpty()) {
            return;
        }

        $investments = [
            [
                'uuid'=> Uuid::uuid4()->toString(),
                'project_id' => $submittedProject->id,
                'investor_id' => $approvedInvestors->first()->id,
                'amount' => 50000.00,
                'token_quantity' => 50.00000000,
                'payment_method' => PaymentMethodEnum::STABLECOIN,
                'payment_currency' => 'USD',
                'payment_reference' => 'BT-2024-001234',
                'status' => InvestmentStatusEnum::COMPLETED,
                'confirmed_at' => now()->subDays(45),
            ],
            [
                'uuid'=> Uuid::uuid4()->toString(),
                'project_id' => $submittedProject->id,
                'investor_id' => $approvedInvestors->last()->id,
                'amount' => 100000.00,
                'token_quantity' => 100.00000000,
                'payment_method' => PaymentMethodEnum::STABLECOIN,
                'payment_currency' => 'USDC',
                'payment_reference' => 'USDC-TX-0x1234abcd',
                'xrpl_tx_hash' => 'XRPL1234567890ABCDEF',
                'status' => InvestmentStatusEnum::COMPLETED,
                'confirmed_at' => now()->subDays(40),
            ],
            [
                'uuid'=> Uuid::uuid4()->toString(),
                'project_id' => $fundingProject?->id ?? $submittedProject->id,
                'investor_id' => $approvedInvestors->first()->id,
                'amount' => 25000.00,
                'token_quantity' => 25.00000000,
                'payment_method' => PaymentMethodEnum::STABLECOIN,
                'payment_currency' => 'USD',
                'payment_reference' => 'BT-2024-005678',
                'status' => InvestmentStatusEnum::CONFIRMED,
                'confirmed_at' => now()->subDays(5),
            ],
            [
                'uuid'=> Uuid::uuid4()->toString(),
                'project_id' => $fundingProject?->id ?? $submittedProject->id,
                'investor_id' => $approvedInvestors->last()->id,
                'amount' => 75000.00,
                'token_quantity' => 75.00000000,
                'payment_method' => PaymentMethodEnum::STABLECOIN,
                'payment_currency' => 'XRP',
                'status' => InvestmentStatusEnum::PENDING,
            ],
            [
                'uuid'=> Uuid::uuid4()->toString(),
                'project_id' => $submittedProject->id,
                'investor_id' => $approvedInvestors->first()->id,
                'amount' => 10000.00,
                'token_quantity' => 10.00000000,
                'payment_method' => PaymentMethodEnum::STABLECOIN,
                'payment_currency' => 'USD',
                'payment_reference' => 'BT-2024-009999',
                'status' => InvestmentStatusEnum::REFUNDED,
                'confirmed_at' => now()->subDays(30),
            ],
        ];

        foreach ($investments as $investmentData) {
            Investment::create($investmentData);
        }
    }
}
