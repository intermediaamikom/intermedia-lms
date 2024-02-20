<?php

namespace App\Filament\Resources\DivisionsResource\Pages;

use App\Filament\Resources\DivisionsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDivisions extends ListRecords
{
    protected static string $resource = DivisionsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
