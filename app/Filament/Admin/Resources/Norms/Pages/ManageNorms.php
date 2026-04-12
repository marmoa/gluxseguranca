<?php

namespace App\Filament\Admin\Resources\Norms\Pages;

use App\Filament\Admin\Resources\Norms\NormResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageNorms extends ManageRecords
{
    protected static string $resource = NormResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
