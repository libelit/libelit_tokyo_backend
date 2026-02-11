<?php

namespace App\Filament\Resources\BlockchainAuditLogs\Pages;

use App\Filament\Resources\BlockchainAuditLogs\BlockchainAuditLogResource;
use Filament\Resources\Pages\ViewRecord;

class ViewBlockchainAuditLog extends ViewRecord
{
    protected static string $resource = BlockchainAuditLogResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
