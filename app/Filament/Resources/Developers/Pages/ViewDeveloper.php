<?php

namespace App\Filament\Resources\Developers\Pages;

use App\Enums\KybStatusEnum;
use App\Filament\Resources\Developers\DeveloperResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Notifications\Notification;

class ViewDeveloper extends ViewRecord
{
    protected static string $resource = DeveloperResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('mark_under_review')
                ->label('Mark Under Review')
                ->color('warning')
                ->icon('heroicon-o-eye')
                ->requiresConfirmation()
                ->modalHeading('Mark KYB as Under Review')
                ->modalDescription('This will indicate that you are actively reviewing this developer\'s KYB documents.')
                ->visible(fn () => $this->record->kyb_status === KybStatusEnum::PENDING)
                ->action(function () {
                    $this->record->update([
                        'kyb_status' => KybStatusEnum::UNDER_REVIEW,
                    ]);

                    Notification::make()
                        ->title('KYB Marked as Under Review')
                        ->success()
                        ->send();
                }),

            Action::make('approve_kyb')
                ->label('Approve KYB')
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->requiresConfirmation()
                ->modalHeading('Approve KYB Verification')
                ->modalDescription('Are you sure you want to approve this developer\'s KYB verification?')
                ->visible(fn () => in_array($this->record->kyb_status, [KybStatusEnum::PENDING, KybStatusEnum::UNDER_REVIEW]))
                ->action(function () {
                    $this->record->update([
                        'kyb_status' => KybStatusEnum::APPROVED,
                        'kyb_approved_at' => now(),
                        'kyb_approved_by' => auth()->id(),
                        'kyb_rejection_reason' => null,
                    ]);

                    Notification::make()
                        ->title('KYB Approved')
                        ->success()
                        ->send();
                }),

            Action::make('reject_kyb')
                ->label('Reject KYB')
                ->color('danger')
                ->icon('heroicon-o-x-circle')
                ->requiresConfirmation()
                ->modalHeading('Reject KYB Verification')
                ->form([
                    \Filament\Forms\Components\Textarea::make('rejection_reason')
                        ->label('Rejection Reason')
                        ->required()
                        ->rows(3),
                ])
                ->visible(fn () => in_array($this->record->kyb_status, [KybStatusEnum::PENDING, KybStatusEnum::UNDER_REVIEW]))
                ->action(function (array $data) {
                    $this->record->update([
                        'kyb_status' => KybStatusEnum::REJECTED,
                        'kyb_rejection_reason' => $data['rejection_reason'],
                    ]);

                    Notification::make()
                        ->title('KYB Rejected')
                        ->danger()
                        ->send();
                }),

            EditAction::make(),
        ];
    }
}
