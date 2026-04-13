<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\TagDistributionResource\Pages;

use App\Filament\Admin\Resources\TagDistributionResource;
use App\Models\TagInventory;
use Filament\Resources\Pages\CreateRecord;

class CreateTagDistribution extends CreateRecord
{
    protected static string $resource = TagDistributionResource::class;

    protected function afterCreate(): void
    {
        // Decrement stock after creating distribution
        $inventory = TagInventory::find($this->record->tag_inventory_id);
        if ($inventory) {
            $qty = (int) $this->record->quantity;
            $inventory->decrement('current_quantity', min($qty, $inventory->current_quantity));
        }
    }
}
