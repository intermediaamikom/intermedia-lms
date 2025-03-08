<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use App\Models\Division;
use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $user = User::with('division')->find(auth()->user()->id);

        return [
            Stat::make(
                'Total Event ' . $user->division->name,
                Event::where(
                    'division_id',
                    $user->division->id
                )->count()
            )->icon('heroicon-o-star'),
            Stat::make(
                'Event Saya',
                Attendance::where(
                    'user_id',
                    $user->id
                )->count()
            )->icon('heroicon-o-star')
                ->description('Total Event yang saya hadiri'),
            Stat::make('Point Keaktivan', $user->total_point)
                ->icon('heroicon-o-star')
                ->description('Total point keaktifan yang dimiliki'),
        ];
    }

    public static function canView(): bool
    {
        return User::find(auth()->user()->id)->hasAnyRole('Admin', 'Member');
    }
}
