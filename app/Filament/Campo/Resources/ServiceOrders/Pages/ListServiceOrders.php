<?php

declare(strict_types=1);

namespace App\Filament\Campo\Resources\ServiceOrders\Pages;

use App\Filament\Campo\Resources\ServiceOrders\ServiceOrderResource;
use Filament\Resources\Pages\ListRecords;

class ListServiceOrders extends ListRecords
{
    protected static string $resource = ServiceOrderResource::class;
}
