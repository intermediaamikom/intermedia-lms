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

        if ($record->points == 0 && $data['points'] != 0) {
            $data['status'] = 'approved';
        }

        if ($data['status'] == 'approved') {
            $user->total_point = max(0, $user->total_point + ($data['points'] - $record->points));
        } else {
            $data['points'] = 0;
            $user->total_point = max(0, $user->total_point - $record->points);
        }
        $user->save();
        $record->update($data);

        return $record;
    }
}
