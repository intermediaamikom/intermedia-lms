<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventResource\Pages;
use App\Filament\Resources\EventResource\RelationManagers;
use App\Models\Attendance;
use App\Models\Division;
use App\Models\Event;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

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
                        ->required(),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('division.name')->searchable()->sortable(),
                TextColumn::make('occasion_date')->sortable(),
                TextColumn::make('start_register')->sortable(),
                TextColumn::make('end_register')->sortable(),
                TextColumn::make('quota')->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('joinEventAction')
                    ->visible(fn (Event $record) => User::find(Auth::user()->id)->attendances->where('event_id', $record->id)->count() == 0)
                    ->mountUsing(fn (Forms\ComponentContainer $form, Event $record) => $form->fill([
                        'division_id' => $record->division->id,
                        'name' => $record->name,
                        'description' => $record->description,
                        'occasion_date' => $record->occasion_date,
                        'quota' => $record->quota,
                        'user_id' => Auth::user()->id
                    ]))
                    ->label('Join')
                    ->form([
                        Select::make('division_id')
                            ->label('Division')
                            ->relationship('division', 'name'),
                        Select::make('user_id')
                            ->label('User')
                            ->options(User::query()->pluck('name', 'id')),
                        TextInput::make('name'),
                        TextInput::make('description'),
                        TextInput::make('occasion_date'),
                        TextInput::make('quota'),
                    ])
                    ->disabledForm()
                    ->icon('heroicon-o-arrow-left-end-on-rectangle')
                    ->action(function (array $data, Event $record) {
                        Attendance::create([
                            'event_id' => $record->id,
                            'user_id' => $data['user_id'],
                        ]);
                    })
                    ->modalAlignment(Alignment::Center)
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
            //
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
}
