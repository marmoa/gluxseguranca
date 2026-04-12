<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\PriceTables\Pages;

use App\Filament\Admin\Resources\PriceTables\PriceTableResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPriceTables extends ListRecords
{
    protected static string $resource = PriceTableResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
