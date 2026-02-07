<?php

namespace App\Filament\Resources\Lenders\Pages;

use App\Enums\AmlStatusEnum;
use App\Enums\KycStatusEnum;
use App\Filament\Resources\Lenders\LenderResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewLender extends ViewRecord
{
    protected static string $resource = LenderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('approve_kyc')
                ->label('Approve KYC')
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->requiresConfirmation()
                ->modalHeading('Approve KYC Verification')
                ->modalDescription('Are you sure you want to approve this lender\'s KYC verification?')
                ->visible(fn () => $this->record->kyc_status !== KycStatusEnum::APPROVED)
                ->action(function () {
                    $this->record->update([
                        'kyc_status' => KycStatusEnum::APPROVED,
                        'kyc_approved_at' => now(),
                        'kyc_approved_by' => auth()->id(),
                    ]);

                    Notification::make()
                        ->title('KYC Approved')
                        ->success()
                        ->send();
                }),

            Action::make('reject_kyc')
                ->label('Reject KYC')
                ->color('danger')
                ->icon('heroicon-o-x-circle')
                ->requiresConfirmation()
                ->modalHeading('Reject KYC Verification')
                ->form([
                    Textarea::make('rejection_reason')
                        ->label('Rejection Reason')
                        ->required()
                        ->rows(3),
                ])
                ->visible(fn () => $this->record->kyc_status !== KycStatusEnum::REJECTED)
                ->action(function (array $data) {
                    $this->record->update([
                        'kyc_status' => KycStatusEnum::REJECTED,
                        'kyc_rejection_reason' => $data['rejection_reason'],
                        'is_active' => false,
                    ]);

                    Notification::make()
                        ->title('KYC Rejected')
                        ->danger()
                        ->send();
                }),

            Action::make('clear_aml')
                ->label('Clear AML')
                ->color('success')
                ->icon('heroicon-o-shield-check')
                ->requiresConfirmation()
                ->visible(fn () => $this->record->aml_status !== AmlStatusEnum::CLEARED)
                ->action(function () {
                    $this->record->update([
                        'aml_status' => AmlStatusEnum::CLEARED,
                        'aml_checked_at' => now(),
                        'is_active' => $this->record->kyc_status === KycStatusEnum::APPROVED,
                    ]);

                    Notification::make()
                        ->title('AML Cleared')
                        ->success()
                        ->send();
                }),

            Action::make('flag_aml')
                ->label('Flag AML')
                ->color('danger')
                ->icon('heroicon-o-flag')
                ->requiresConfirmation()
                ->modalDescription('This will flag this lender for AML concerns and deactivate their account.')
                ->visible(fn () => $this->record->aml_status !== AmlStatusEnum::FLAGGED)
                ->action(function () {
                    $this->record->update([
                        'aml_status' => AmlStatusEnum::FLAGGED,
                        'aml_checked_at' => now(),
                        'is_active' => false,
                    ]);

                    Notification::make()
                        ->title('AML Flagged')
                        ->danger()
                        ->send();
                }),

            EditAction::make(),
        ];
    }
}
