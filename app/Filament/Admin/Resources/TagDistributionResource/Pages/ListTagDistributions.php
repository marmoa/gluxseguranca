<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\TagDistributionResource\Pages;

use App\Filament\Admin\Resources\TagDistributionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTagDistributions extends ListRecords
{
    protected static string $resource = TagDistributionResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
