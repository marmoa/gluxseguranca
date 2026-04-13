<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\TagInventoryResource\Pages;

use App\Filament\Admin\Resources\TagInventoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTagInventory extends CreateRecord
{
    protected static string $resource = TagInventoryResource::class;
}
