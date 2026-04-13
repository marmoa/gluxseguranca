<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\TagInventoryResource\Pages;

use App\Filament\Admin\Resources\TagInventoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTagInventories extends ListRecords
{
    protected static string $resource = TagInventoryResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
