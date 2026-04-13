<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\TagInventoryResource\Pages;

use App\Filament\Admin\Resources\TagInventoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTagInventory extends EditRecord
{
    protected static string $resource = TagInventoryResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
