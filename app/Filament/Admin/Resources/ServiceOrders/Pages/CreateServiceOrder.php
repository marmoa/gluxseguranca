<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\ServiceOrders\Pages;

use App\Filament\Admin\Resources\ServiceOrders\ServiceOrderResource;
use App\Services\ServiceOrderNumberService;
use Filament\Resources\Pages\CreateRecord;

class CreateServiceOrder extends CreateRecord
{
    protected static string $resource = ServiceOrderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['number'] = app(ServiceOrderNumberService::class)->generate();

        return $data;
    }
}
