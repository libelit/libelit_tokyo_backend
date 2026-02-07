<?php

namespace App\Filament\Resources\Users\Pages;

use App\Enums\UserTypeEnum;
use App\Filament\Resources\Users\UserResource;
use App\Models\DeveloperProfile;
use App\Models\LenderProfile;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function afterCreate(): void
    {
        $user = $this->record;
        $companyName = $this->data['company_name'] ?? null;

        match ($user->type) {
            UserTypeEnum::DEVELOPER => DeveloperProfile::create([
                'user_id' => $user->id,
                'company_name' => $companyName,
            ]),
            UserTypeEnum::LENDER => LenderProfile::create([
                'user_id' => $user->id,
                'company_name' => $companyName,
            ]),
            default => null,
        };
    }
}
