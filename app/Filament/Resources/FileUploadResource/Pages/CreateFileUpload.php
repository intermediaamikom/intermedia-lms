<?php

namespace App\Filament\Resources\FileUploadResource\Pages;

use App\Filament\Resources\FileUploadResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFileUpload extends CreateRecord
{
    protected static string $resource = FileUploadResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        return $data;
    }
}
