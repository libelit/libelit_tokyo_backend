<?php

namespace App\Filament\Resources\Investments\Pages;

use App\Enums\InvestmentStatusEnum;
use App\Filament\Resources\Investments\InvestmentResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewInvestment extends ViewRecord
{
    protected static string $resource = InvestmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('confirm')
                ->label('Confirm Investment')
                ->icon('heroicon-o-check-circle')
                ->color('info')
                ->visible(fn () => $this->record->status === InvestmentStatusEnum::PENDING)
                ->requiresConfirmation()
                ->modalHeading('Confirm Investment')
                ->modalDescription('Are you sure you want to confirm this investment?')
                ->action(function () {
                    $this->record->update([
                        'status' => InvestmentStatusEnum::CONFIRMED,
                        'confirmed_at' => now(),
                    ]);

                    Notification::make()
                        ->title('Investment Confirmed')
                        ->body('The investment has been confirmed.')
                        ->success()
                        ->send();
                }),

            Action::make('complete')
                ->label('Mark Completed')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->visible(fn () => $this->record->status === InvestmentStatusEnum::CONFIRMED)
                ->requiresConfirmation()
                ->modalHeading('Complete Investment')
                ->modalDescription('Mark this investment as completed? This indicates tokens have been transferred.')
                ->action(function () {
                    $this->record->update([
                        'status' => InvestmentStatusEnum::COMPLETED,
                    ]);

                    Notification::make()
                        ->title('Investment Completed')
                        ->body('The investment has been marked as completed.')
                        ->success()
                        ->send();
                }),

            Action::make('refund')
                ->label('Refund')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('danger')
                ->visible(fn () => in_array($this->record->status, [
                    InvestmentStatusEnum::PENDING,
                    InvestmentStatusEnum::CONFIRMED,
                ]))
                ->requiresConfirmation()
                ->modalHeading('Refund Investment')
                ->modalDescription('Are you sure you want to refund this investment? This action should be accompanied by actual fund return.')
                ->action(function () {
                    $this->record->update([
                        'status' => InvestmentStatusEnum::REFUNDED,
                    ]);

                    Notification::make()
                        ->title('Investment Refunded')
                        ->body('The investment has been marked as refunded.')
                        ->warning()
                        ->send();
                }),

            EditAction::make(),
            DeleteAction::make(),
        ];
    }
}
