<?php

declare(strict_types=1);

namespace App\Filament\Comum\Resources\ServiceOrders\Pages;

use App\Filament\Comum\Resources\ServiceOrders\ServiceOrderResource;
use Filament\Resources\Pages\ViewRecord;

class ViewServiceOrder extends ViewRecord
{
    protected static string $resource = ServiceOrderResource::class;
}
