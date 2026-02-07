<?php

namespace App\Filament\Resources\Projects\Pages;

use App\Enums\KybStatusEnum;
use App\Filament\Resources\Projects\ProjectResource;
use App\Models\DeveloperProfile;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreateProject extends CreateRecord
{
    protected static string $resource = ProjectResource::class;

    protected function beforeCreate(): void
    {
        $developerId = $this->data['developer_id'];
        $developer = DeveloperProfile::find($developerId);

        if (!$developer || $developer->kyb_status !== KybStatusEnum::APPROVED) {
            Notification::make()
                ->title('Cannot create project')
                ->body('The selected developer has not completed KYB verification.')
                ->danger()
                ->send();

            throw ValidationException::withMessages([
                'developer_id' => 'The selected developer must have approved KYB status.',
            ]);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
