<?php

namespace App\Filament\Resources\Lenders\RelationManagers;

use App\Enums\DocumentTypeEnum;
use App\Enums\VerificationStatusEnum;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

    protected static ?string $title = 'KYC Documents';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('document_type')
                    ->options([
                        DocumentTypeEnum::KYC_ID->value => 'ID Document',
                        DocumentTypeEnum::KYC_ADDRESS_PROOF->value => 'Address Proof',
                        DocumentTypeEnum::KYC_ACCREDITATION->value => 'Accreditation Proof',
                    ])
                    ->required(),
                TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                FileUpload::make('file_path')
                    ->label('Document')
                    ->directory('documents/kyc')
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
                TextColumn::make('verification_status')
                    ->badge()
                    ->color(fn (VerificationStatusEnum $state): string => match ($state) {
                        VerificationStatusEnum::PENDING => 'warning',
                        VerificationStatusEnum::VERIFIED => 'success',
                        VerificationStatusEnum::REJECTED => 'danger',
                    }),
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
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
