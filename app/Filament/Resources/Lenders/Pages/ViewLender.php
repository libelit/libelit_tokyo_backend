<?php

namespace App\Filament\Resources\Lenders\Pages;

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
            Action::make('mark_under_review')
                ->label('Mark Under Review')
                ->color('warning')
                ->icon('heroicon-o-eye')
                ->requiresConfirmation()
                ->modalHeading('Mark KYC as Under Review')
                ->modalDescription('This will indicate that you are actively reviewing this lender\'s KYC documents.')
                ->visible(fn () => $this->record->kyc_status === KycStatusEnum::PENDING)
                ->action(function () {
                    $this->record->update([
                        'kyc_status' => KycStatusEnum::UNDER_REVIEW,
                    ]);

                    Notification::make()
                        ->title('KYC Marked as Under Review')
                        ->success()
                        ->send();
                }),

            Action::make('approve_kyc')
                ->label('Approve KYC')
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->requiresConfirmation()
                ->modalHeading('Approve KYC Verification')
                ->modalDescription('Are you sure you want to approve this lender\'s KYC verification?')
                ->visible(fn () => in_array($this->record->kyc_status, [KycStatusEnum::PENDING, KycStatusEnum::UNDER_REVIEW]))
                ->action(function () {
                    $this->record->update([
                        'kyc_status' => KycStatusEnum::APPROVED,
                        'kyc_approved_at' => now(),
                        'kyc_approved_by' => auth()->id(),
                        'kyc_rejection_reason' => null,
                        'is_active' => true,
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
                ->visible(fn () => in_array($this->record->kyc_status, [KycStatusEnum::PENDING, KycStatusEnum::UNDER_REVIEW]))
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

            EditAction::make(),
        ];
    }
}
