<?php

namespace App\Filament\Resources\EventResource\Pages;

use App\Filament\Resources\EventResource;
use App\Models\Division;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateEvent extends CreateRecord
{
    protected static string $resource = EventResource::class;

    function mutateFormDataBeforeCreate(array $data): array
    {
        $user = User::find(auth()->user()->id);

        $division_id = $user->division_id ?: Division::first()->id;

        if ($user->hasRole('Super Admin')) {
            $division_id = $data['division_id'] ?: $user->division_id ?: Division::first()->id;
        }

        $data['division_id'] = $division_id;
        return $data;
    }
}
