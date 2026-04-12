<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\PriceTables\Pages;

use App\Filament\Admin\Resources\PriceTables\PriceTableResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditPriceTable extends EditRecord
{
    protected static string $resource = PriceTableResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
