<?php

declare(strict_types=1);

namespace App\Filament\Comum\Resources\Invoices\Pages;

use App\Filament\Comum\Resources\Invoices\InvoiceResource;
use Filament\Resources\Pages\ViewRecord;

class ViewInvoice extends ViewRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
