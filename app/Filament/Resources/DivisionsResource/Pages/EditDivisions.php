<?php

namespace App\Filament\Resources\DivisionsResource\Pages;

use App\Filament\Resources\DivisionsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDivisions extends EditRecord
{
    protected static string $resource = DivisionsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
