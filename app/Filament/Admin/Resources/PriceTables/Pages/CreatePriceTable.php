<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\PriceTables\Pages;

use App\Filament\Admin\Resources\PriceTables\PriceTableResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePriceTable extends CreateRecord
{
    protected static string $resource = PriceTableResource::class;
}
