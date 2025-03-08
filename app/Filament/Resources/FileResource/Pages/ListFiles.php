<?php

namespace App\Filament\Resources\FileResource\Pages;

use App\Filament\Resources\FileResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ListFiles extends ListRecords
{
    protected static string $resource = FileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    function getTabs(): array
    {
        $user = User::find(auth()->user()->id);
        if ($user->hasRole('Admin') || $user->hasRole('Super Admin')) {
            return [
                'new' => Tab::make('New')
                    ->modifyQueryUsing(
                        fn(Builder $query) => $query->where('status', 'pending')
                    ),
                'history' => Tab::make('History')
                    ->modifyQueryUsing(
                        fn(Builder $query) => $query->where('status', '!=', 'pending')
                    ),
            ];
        }

        return [
            'pending' => Tab::make('Pending')
                ->modifyQueryUsing(
                    fn(Builder $query) => $query->where('status', 'pending')->where('user_id', $user->id)
                ),
            'approved' => Tab::make('Approved')
                ->modifyQueryUsing(
                    fn(Builder $query) => $query->where('status', 'approved')->where('user_id', $user->id)
                ),
            'rejected' => Tab::make('Rejected')
                ->modifyQueryUsing(
                    fn(Builder $query) => $query->where('status', 'rejected')->where('user_id', $user->id)
                ),
        ];
    }
}
