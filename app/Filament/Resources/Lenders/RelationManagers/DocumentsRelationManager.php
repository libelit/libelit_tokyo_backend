<?php

namespace App\Filament\Resources\Lenders\RelationManagers;

use App\Enums\DocumentTypeEnum;
use App\Enums\VerificationStatusEnum;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

    protected static ?string $title = 'KYB Documents';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('document_type')
                    ->options([
                        DocumentTypeEnum::KYB_LENDER_CERTIFICATE_OF_INCORPORATION->value => 'Certificate of Incorporation',
                        DocumentTypeEnum::KYB_LENDER_BUSINESS_LICENSE->value => 'Business/Financial Services License',
                        DocumentTypeEnum::KYB_LENDER_BENEFICIAL_OWNERSHIP->value => 'Beneficial Ownership Declaration',
                        DocumentTypeEnum::KYB_LENDER_TAX_CERTIFICATE->value => 'Tax Identification Certificate',
                        DocumentTypeEnum::KYB_LENDER_ADDRESS_PROOF->value => 'Proof of Business Address',
                    ])
                    ->required(),
                TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                FileUpload::make('file_path')
                    ->label('Document')
                    ->directory('documents/lender_kyb')
                    ->required()
                    ->acceptedFileTypes(['application/pdf', 'image/*'])
                    ->maxSize(10240),
                Select::make('verification_status')
                    ->options(VerificationStatusEnum::class)
                    ->default(VerificationStatusEnum::PENDING),
                Toggle::make('is_public')
                    ->label('Publicly Visible'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('document_type')
                    ->badge(),
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('file_url')
                    ->label('Document')
                    ->url(fn ($record) => $record->file_url)
                    ->openUrlInNewTab()
                    ->formatStateUsing(fn () => 'View/Download')
                    ->color('primary'),
                TextColumn::make('verification_status')
                    ->badge()
                    ->color(fn (VerificationStatusEnum $state): string => match ($state) {
                        VerificationStatusEnum::PENDING => 'warning',
                        VerificationStatusEnum::APPROVED => 'success',
                        VerificationStatusEnum::REJECTED => 'danger',
                    }),
                TextColumn::make('formatted_file_size')
                    ->label('Size'),
                IconColumn::make('is_public')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['uploaded_by'] = auth()->id();

                        if (!empty($data['file_path'])) {
                            $filePath = $data['file_path'];
                            $data['file_name'] = basename($filePath);

                            if (Storage::disk('public')->exists($filePath)) {
                                $data['file_size'] = Storage::disk('public')->size($filePath);
                                $data['mime_type'] = Storage::disk('public')->mimeType($filePath);
                            }
                        }

                        return $data;
                    }),
            ])
            ->actions([
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->verification_status !== VerificationStatusEnum::APPROVED)
                    ->action(function ($record) {
                        $record->update([
                            'verification_status' => VerificationStatusEnum::APPROVED,
                            'verified_by' => auth()->id(),
                            'verified_at' => now(),
                            'rejection_reason' => null,
                        ]);

                        Notification::make()
                            ->title('Document Approved')
                            ->success()
                            ->send();
                    }),
                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->form([
                        Textarea::make('rejection_reason')
                            ->label('Rejection Reason')
                            ->required()
                            ->rows(3),
                    ])
                    ->visible(fn ($record) => $record->verification_status !== VerificationStatusEnum::REJECTED)
                    ->action(function ($record, array $data) {
                        $record->update([
                            'verification_status' => VerificationStatusEnum::REJECTED,
                            'verified_by' => auth()->id(),
                            'verified_at' => now(),
                            'rejection_reason' => $data['rejection_reason'],
                        ]);

                        Notification::make()
                            ->title('Document Rejected')
                            ->danger()
                            ->send();
                    }),
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
