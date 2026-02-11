<?php

namespace App\Filament\Resources\Lenders\Pages;

use App\Enums\BlockchainAuditEventTypeEnum;
use App\Enums\KybStatusEnum;
use App\Filament\Resources\Lenders\LenderResource;
use App\Managers\AuditTrailManager;
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
                ->modalHeading('Mark KYB as Under Review')
                ->modalDescription('This will indicate that you are actively reviewing this lender\'s KYB documents.')
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
                ->modalDescription('Are you sure you want to approve this lender\'s KYB verification?')
                ->visible(fn () => in_array($this->record->kyb_status, [KybStatusEnum::PENDING, KybStatusEnum::UNDER_REVIEW]))
                ->action(function () {
                    $this->record->update([
                        'kyb_status' => KybStatusEnum::APPROVED,
                        'kyb_approved_at' => now(),
                        'kyb_approved_by' => auth()->id(),
                        'kyb_rejection_reason' => null,
                        'is_active' => true,
                    ]);

                    AuditTrailManager::record(
                        BlockchainAuditEventTypeEnum::LENDER_KYB_APPROVED,
                        $this->record,
                        ['approved_by' => auth()->id()]
                    );

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
                    Textarea::make('rejection_reason')
                        ->label('Rejection Reason')
                        ->required()
                        ->rows(3),
                ])
                ->visible(fn () => in_array($this->record->kyb_status, [KybStatusEnum::PENDING, KybStatusEnum::UNDER_REVIEW]))
                ->action(function (array $data) {
                    $this->record->update([
                        'kyb_status' => KybStatusEnum::REJECTED,
                        'kyb_rejection_reason' => $data['rejection_reason'],
                        'is_active' => false,
                    ]);

                    AuditTrailManager::record(
                        BlockchainAuditEventTypeEnum::LENDER_KYB_REJECTED,
                        $this->record,
                        ['rejection_reason' => $data['rejection_reason']]
                    );

                    Notification::make()
                        ->title('KYB Rejected')
                        ->danger()
                        ->send();
                }),

            EditAction::make(),
        ];
    }
}
