<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\TagDistributionResource\Pages;

use App\Filament\Admin\Resources\TagDistributionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTagDistribution extends EditRecord
{
    protected static string $resource = TagDistributionResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
