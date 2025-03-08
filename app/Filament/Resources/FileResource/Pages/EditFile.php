<?php

namespace App\Filament\Resources\FileResource\Pages;

use App\Filament\Resources\FileResource;
use App\Models\File;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditFile extends EditRecord
{
    protected static string $resource = FileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
 
    protected function handleRecordUpdate(Model $record, array $data): File
    {
        $user = User::find($record->user_id);
        $user->total_point = $user->total_point + ($data['points'] - $record->points);
        $user->save();
        $record->update($data);
    
        return $record;
    }


}
