<?php

namespace App\Filament\Resources\FileUploadResource\Pages;

use App\Filament\Resources\FileUploadResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditFileUpload extends EditRecord
{
    protected static string $resource = FileUploadResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if ($data['status'] === 'accepted') {
            $user = $this->record->user;
            $user->memberPoints()->create([
                'description' => 'File upload accepted',
                'point' => 10, // Example point value
            ]);
        }

        return $data;
    }

    protected function afterSave(): void
    {
        Notification::make()
            ->title('File upload status updated')
            ->success()
            ->send();
    }
}
