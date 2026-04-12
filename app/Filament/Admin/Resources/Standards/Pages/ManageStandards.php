<?php

namespace App\Filament\Admin\Resources\Standards\Pages;

use App\Filament\Admin\Resources\Standards\StandardResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageStandards extends ManageRecords
{
    protected static string $resource = StandardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
