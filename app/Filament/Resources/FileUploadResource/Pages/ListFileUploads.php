<?php

namespace App\Filament\Resources\FileUploadResource\Pages;

use App\Filament\Resources\FileUploadResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFileUploads extends ListRecords
{
    protected static string $resource = FileUploadResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
