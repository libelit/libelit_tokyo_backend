<?php

namespace App\Filament\Resources\Projects\Pages;

use App\Enums\ProjectStatusEnum;
use App\Enums\VerificationStatusEnum;
use App\Filament\Resources\Projects\ProjectResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewProject extends ViewRecord
{
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Start Review Action
            Action::make('startReview')
                ->label('Start Review')
                ->icon('heroicon-o-eye')
                ->color('info')
                ->visible(fn () => $this->record->status === ProjectStatusEnum::SUBMITTED)
                ->requiresConfirmation()
                ->modalHeading('Start Review')
                ->modalDescription('Are you sure you want to start reviewing this project?')
                ->action(function () {
                    $this->record->update([
                        'status' => ProjectStatusEnum::UNDER_REVIEW,
                    ]);

                    Notification::make()
                        ->title('Review Started')
                        ->body('The project is now under review.')
                        ->success()
                        ->send();
                }),

            // Approve Action
            Action::make('approve')
                ->label('Approve')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () => in_array($this->record->status, [
                    ProjectStatusEnum::SUBMITTED,
                    ProjectStatusEnum::UNDER_REVIEW,
                ]))
                ->disabled(function () {
                    // Disable if no documents or any document is not approved
                    $documents = $this->record->documents;
                    if ($documents->isEmpty()) {
                        return true;
                    }
                    return $documents->contains(fn ($doc) => $doc->verification_status !== VerificationStatusEnum::APPROVED);
                })
                ->tooltip(function () {
                    $documents = $this->record->documents;
                    if ($documents->isEmpty()) {
                        return 'No documents uploaded. Please upload and approve documents first.';
                    }
                    $pendingCount = $documents->filter(fn ($doc) => $doc->verification_status !== VerificationStatusEnum::APPROVED)->count();
                    if ($pendingCount > 0) {
                        return "{$pendingCount} document(s) pending approval. All documents must be approved first.";
                    }
                    return null;
                })
                ->requiresConfirmation()
                ->modalHeading('Approve Project')
                ->modalDescription('Are you sure you want to approve this project? You will need to list it on the marketplace for lenders to see it.')
                ->action(function () {
                    $this->record->update([
                        'status' => ProjectStatusEnum::APPROVED,
                        'approved_at' => now(),
                        'approved_by' => auth()->id(),
                        'rejection_reason' => null,
                    ]);

                    Notification::make()
                        ->title('Project Approved')
                        ->body('The project has been approved. You can now list it on the marketplace.')
                        ->success()
                        ->send();
                }),

            // Reject Action
            Action::make('reject')
                ->label('Reject')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn () => in_array($this->record->status, [
                    ProjectStatusEnum::SUBMITTED,
                    ProjectStatusEnum::UNDER_REVIEW,
                ]))
                ->form([
                    Textarea::make('rejection_reason')
                        ->label('Rejection Reason')
                        ->required()
                        ->rows(3)
                        ->placeholder('Please provide a reason for rejection...'),
                ])
                ->modalHeading('Reject Project')
                ->action(function (array $data) {
                    $this->record->update([
                        'status' => ProjectStatusEnum::REJECTED,
                        'rejection_reason' => $data['rejection_reason'],
                    ]);

                    Notification::make()
                        ->title('Project Rejected')
                        ->body('The project has been rejected.')
                        ->warning()
                        ->send();
                }),

            // List on Marketplace Action
            Action::make('listOnMarketplace')
                ->label('List on Marketplace')
                ->icon('heroicon-o-shopping-bag')
                ->color('primary')
                ->visible(fn () => $this->record->status === ProjectStatusEnum::APPROVED)
                ->requiresConfirmation()
                ->modalHeading('List on Marketplace')
                ->modalDescription('Are you sure you want to list this project on the marketplace? It will become visible to all lenders.')
                ->action(function () {
                    $this->record->update([
                        'status' => ProjectStatusEnum::LISTED,
                        'listed_at' => now(),
                    ]);

                    Notification::make()
                        ->title('Project Listed')
                        ->body('The project has been listed on the marketplace and is now visible to lenders.')
                        ->success()
                        ->send();
                }),

            // Mark as Funded
            Action::make('markFunded')
                ->label('Mark as Funded')
                ->icon('heroicon-o-banknotes')
                ->color('success')
                ->visible(fn () => $this->record->status === ProjectStatusEnum::LISTED)
                ->requiresConfirmation()
                ->modalHeading('Mark as Funded')
                ->modalDescription('Confirm that this project has been fully funded?')
                ->action(function () {
                    $this->record->update([
                        'status' => ProjectStatusEnum::FUNDED,
                        'funded_at' => now(),
                    ]);

                    Notification::make()
                        ->title('Project Funded')
                        ->body('The project has been marked as fully funded.')
                        ->success()
                        ->send();
                }),

            // Mark as Completed
            Action::make('markCompleted')
                ->label('Mark Completed')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->visible(fn () => $this->record->status === ProjectStatusEnum::FUNDED)
                ->requiresConfirmation()
                ->modalHeading('Complete Project')
                ->modalDescription('Mark this project as completed? This indicates the investment cycle is finished.')
                ->action(function () {
                    $this->record->update([
                        'status' => ProjectStatusEnum::COMPLETED,
                    ]);

                    Notification::make()
                        ->title('Project Completed')
                        ->body('The project investment cycle has been marked as completed.')
                        ->success()
                        ->send();
                }),

            EditAction::make(),
            DeleteAction::make(),
            RestoreAction::make(),
            ForceDeleteAction::make(),
        ];
    }
}
