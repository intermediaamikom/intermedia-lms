<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventResource\Pages;
use App\Filament\Resources\EventResource\RelationManagers;
use App\Models\Attendance;
use App\Models\Division;
use App\Models\Event;
use App\Models\User;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\StaticAction;
use Filament\Forms;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()->schema([
                    TextInput::make('name')->required()->placeholder('Name')->autofocus(),
                    Textarea::make('description')->required()->placeholder('Description'),
                    TextInput::make('occasion_date')
                        ->type('datetime-local')
                        ->required()
                        ->placeholder('Occasion Date'),
                    TextInput::make('start_register')
                        ->type('datetime-local')
                        ->required()
                        ->placeholder('Start Register'),
                    TextInput::make('end_register')
                        ->type('datetime-local')
                        ->required()
                        ->placeholder('Start Register'),
                    TextInput::make('quota')
                        ->type('number')
                        ->required()
                        ->placeholder('Quota'),
                ]),
                Section::make()->schema([
                    Select::make('division_id')
                        ->relationship('division', 'name')
                ])->visible(fn () => User::find(auth()->user()->id)->hasRole('Super Admin'))
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('division.name')->searchable()->sortable(),
                TextColumn::make('occasion_date')->sortable()->dateTime(),
                TextColumn::make('start_register')->sortable()->dateTime(),
                TextColumn::make('end_register')->sortable()->dateTime(),
                TextColumn::make('quota')->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('detail')
                    ->label('Detail')
                    ->icon('heroicon-o-eye')
                    ->color(Color::Gray)
                    // ->disabled(fn (Event $record) => $record->quota == 0)
                    ->visible(fn (Event $record) => $record->users->contains(auth()->user()))
                    ->mountUsing(fn (Forms\ComponentContainer $form, Event $record) => $form->fill([
                        'name' => $record->name,
                        'is_competence' => $record->attendances()->where('user_id', auth()->user()->id)->first()->is_competence,
                        'certificate_link' => $record->attendances()->where('user_id', auth()->user()->id)->first()->certificate_link,
                        'final_project_link' => $record->attendances()->where('user_id', auth()->user()->id)->first()->final_project_link,
                        'submission_score' => $record->attendances()->where('user_id', auth()->user()->id)->first()->submission_score,
                        'participation_score' => $record->attendances()->where('user_id', auth()->user()->id)->first()->participation_score,
                    ]))
                    ->form([
                        TextInput::make('name')->readOnly(),
                        TextInput::make('final_project_link')->url()->label('Final Project Link'),
                        Checkbox::make('is_competence')->label('Competence')->disabled(),
                        TextInput::make('certificate_link')->url()->label('Certificate Link')->readOnly(),
                        TextInput::make('submission_score')->url()->label('Submission Score')->readOnly(),
                        TextInput::make('participation_score')->url()->label('Participation Score')->readOnly(),
                    ])
                    ->action(function (array $data, Event $record) {
                        $attendance = $record->attendances->where('user_id', auth()->user()->id)->first();
                        $attendance->final_project_link = $data['final_project_link'];
                        $attendance->save();
                    })
                    ->modalSubmitAction(fn (StaticAction $action) => $action->label('Submit Final Project')),
                Tables\Actions\Action::make('joinEventAction')
                    ->label('Join')
                    ->icon('heroicon-o-arrow-left-end-on-rectangle')
                    ->disabled(fn (Event $record) => $record->quota == 0 || (Carbon::now()->lt(Carbon::parse($record->start_register)) || Carbon::now()->gt($record->end_register)))
                    ->visible(fn (Event $record) => !$record->users->contains(auth()->user()) || User::find(auth()->user()->id)->division_id != $record->division_id)
                    ->mountUsing(fn (Forms\ComponentContainer $form, Event $record) => $form->fill([
                        'name' => $record->name,
                        'description' => $record->description,
                        'occasion_date' => $record->occasion_date,
                        'quota' => $record->quota,
                        'user_id' => Auth::user()->id
                    ]))
                    ->form([
                        Select::make('user_id')
                            ->label('Join As')
                            ->options(User::query()->pluck('name', 'id')),
                        TextInput::make('name'),
                        Textarea::make('description'),
                        TextInput::make('occasion_date')
                    ])
                    ->action(function (array $data, Event $record) {
                        Attendance::create([
                            'event_id' => $record->id,
                            'user_id' => $data['user_id'],
                        ]);

                        $record->quota -= 1;
                        $record->save();
                    })
                    ->disabledForm()
                    ->modalAlignment(Alignment::Center)
                    ->modalSubmitAction(fn (StaticAction $action) => $action->label('Join Event')),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            EventResource\RelationManagers\UsersRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEvents::route('/'),
            'create' => Pages\CreateEvent::route('/create'),
            'edit' => Pages\EditEvent::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $user = User::find(auth()->user()->id);
        if ($user->hasRole('Super Admin')) {
            return parent::getEloquentQuery();
        }

        return parent::getEloquentQuery()->where('division_id', $user->division_id);
    }
}
