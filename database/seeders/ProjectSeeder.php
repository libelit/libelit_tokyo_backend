<?php

namespace Database\Seeders;

use App\Enums\ProjectStatusEnum;
use App\Enums\ProjectTypeEnum;
use App\Models\Project;
use App\Models\DeveloperProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Ramsey\Uuid\Uuid;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $approvedDeveloper = DeveloperProfile::whereHas('user', function ($query) {
            $query->where('email', 'owner1@test.com');
        })->first();

        $underReviewDeveloper = DeveloperProfile::whereHas('user', function ($query) {
            $query->where('email', 'owner2@test.com');
        })->first();

        $admin = User::where('email', 'admin@libelit.com')->first();

        if (!$approvedDeveloper) {
            return;
        }

        $projects = [
            [
                'uuid'=> Uuid::uuid4()->toString(),
                'developer_id' => $approvedDeveloper->id,
                'title' => 'Dubai Marina Luxury Tower',
                'description' => 'A premium 25-story residential tower in Dubai Marina offering stunning waterfront views. Features include a rooftop infinity pool, state-of-the-art fitness center, and 24/7 concierge service.',
                'project_type' => ProjectTypeEnum::RESIDENTIAL,
                'city' => 'Dubai',
                'country' => 'UAE',
                'address' => 'Dubai Marina, Plot 45, Dubai, UAE',
                'funding_goal' => 5000000.00,
                'currency' => 'USD',
                'min_investment' => 1000.00,
                'expected_return' => 12.50,
                'loan_term_months' => 24,
                'ltv_ratio' => 65.00,
                'risk_score' => 2,
                'status' => ProjectStatusEnum::DRAFT,
                'submitted_at' => now()->subDays(60),
                'approved_at' => now()->subDays(55),
                'approved_by' => $admin?->id,
                'listed_at' => now()->subDays(50),
            ],
            [
                'uuid'=> Uuid::uuid4()->toString(),
                'developer_id' => $approvedDeveloper->id,
                'title' => 'Business Bay Commercial Complex',
                'description' => 'A modern 15-floor commercial building in the heart of Business Bay. Perfect for corporate offices with Grade A specifications, underground parking, and smart building technology.',
                'project_type' => ProjectTypeEnum::COMMERCIAL,
                'city' => 'Dubai',
                'country' => 'UAE',
                'address' => 'Business Bay, Tower Road, Dubai, UAE',
                'funding_goal' => 8500000.00,
                'currency' => 'USD',
                'min_investment' => 5000.00,
                'expected_return' => 10.00,
                'loan_term_months' => 36,
                'ltv_ratio' => 70.00,
                'risk_score' => 4,
                'status' => ProjectStatusEnum::FUNDING,
                'submitted_at' => now()->subDays(45),
                'approved_at' => now()->subDays(40),
                'approved_by' => $admin?->id,
                'listed_at' => now()->subDays(30),
            ],
            [
                'uuid'=> Uuid::uuid4()->toString(),
                'developer_id' => $approvedDeveloper->id,
                'title' => 'Palm Jumeirah Villa Collection',
                'description' => 'An exclusive collection of 5 beachfront villas on Palm Jumeirah. Each villa features private beach access, infinity pool, and panoramic views of the Arabian Gulf.',
                'project_type' => ProjectTypeEnum::RESIDENTIAL,
                'city' => 'Dubai',
                'country' => 'UAE',
                'address' => 'Palm Jumeirah, Frond N, Dubai, UAE',
                'funding_goal' => 15000000.00,
                'currency' => 'USD',
                'min_investment' => 10000.00,
                'expected_return' => 15.00,
                'loan_term_months' => 48,
                'ltv_ratio' => 60.00,
                'risk_score' => 1,
                'status' => ProjectStatusEnum::SUBMITTED,
                'submitted_at' => now()->subDays(3),
            ],
            [
                'uuid'=> Uuid::uuid4()->toString(),
                'developer_id' => $underReviewDeveloper?->id ?? $approvedDeveloper->id,
                'title' => 'Manhattan Mixed-Use Development',
                'description' => 'A 30-story mixed-use development in Midtown Manhattan featuring retail space on lower floors and luxury apartments above. Prime location near Central Park.',
                'project_type' => ProjectTypeEnum::MIXED_USE,
                'city' => 'New York',
                'country' => 'USA',
                'address' => '425 Park Avenue, New York, NY 10022',
                'funding_goal' => 25000000.00,
                'currency' => 'USD',
                'min_investment' => 25000.00,
                'expected_return' => 8.50,
                'loan_term_months' => 60,
                'ltv_ratio' => 55.00,
                'risk_score' => 5,
                'status' => ProjectStatusEnum::UNDER_REVIEW,
                'submitted_at' => now()->subDays(10),
            ],
            [
                'uuid'=> Uuid::uuid4()->toString(),
                'developer_id' => $approvedDeveloper->id,
                'title' => 'Industrial Warehouse Complex',
                'description' => 'A modern logistics and warehouse facility spanning 50,000 sqft in Jebel Ali Free Zone. Ideal for e-commerce fulfillment with direct port access.',
                'project_type' => ProjectTypeEnum::INDUSTRIAL,
                'city' => 'Dubai',
                'country' => 'UAE',
                'address' => 'Jebel Ali Free Zone, Plot 234, Dubai, UAE',
                'funding_goal' => 3500000.00,
                'currency' => 'USD',
                'min_investment' => 2500.00,
                'expected_return' => 11.00,
                'loan_term_months' => 24,
                'ltv_ratio' => 75.00,
                'risk_score' => 7,
                'status' => ProjectStatusEnum::DRAFT,
            ],
        ];

        foreach ($projects as $projectData) {
            Project::updateOrCreate(
                ['title' => $projectData['title']],
                $projectData
            );
        }
    }
}
