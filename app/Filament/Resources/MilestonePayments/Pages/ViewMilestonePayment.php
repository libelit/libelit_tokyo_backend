<?php

namespace App\Filament\Resources\MilestonePayments\Pages;

use App\Enums\MilestoneStatusEnum;
use App\Filament\Resources\MilestonePayments\MilestonePaymentResource;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewMilestonePayment extends ViewRecord
{
    protected static string $resource = MilestonePaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('mark_as_paid')
                ->label('Mark as Paid')
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->requiresConfirmation()
                ->modalHeading('Mark Milestone as Paid')
                ->modalDescription('Enter the payment reference to mark this milestone as paid.')
                ->form([
                    TextInput::make('payment_reference')
                        ->label('Payment Reference')
                        ->placeholder('e.g., Wire transfer #12345, Check #9876')
                        ->required()
                        ->maxLength(255),
                ])
                ->visible(fn () => $this->record->status === MilestoneStatusEnum::APPROVED)
                ->action(function (array $data) {
                    $this->record->update([
                        'status' => MilestoneStatusEnum::PAID,
                        'paid_at' => now(),
                        'payment_reference' => $data['payment_reference'],
                    ]);

                    Notification::make()
                        ->title('Milestone marked as paid')
                        ->success()
                        ->send();
                }),
        ];
    }
}
