<?php

namespace App\Filament\Resources\EventResource\Pages;

use App\Filament\Resources\EventResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ListEvents extends ListRecords
{
    protected static string $resource = EventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    function getTabs(): array
    {
        return [
            'all' => Tab::make('All'),
            'joined' => Tab::make('Joined')
                ->modifyQueryUsing(
                    fn (Builder $query) => $query->whereIn('id', DB::table('attendances')->select('event_id')->where('user_id', auth()->user()->id))
                ),
        ];
    }
}
