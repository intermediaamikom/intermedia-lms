<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Filament\Resources\UserResource\RelationManagers\DivisionRelationManager;
use App\Models\Division;
use App\Models\Role;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Actions\SelectAction;
use Filament\Pages\Page;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Resource;
use Filament\Support\View\Components\Modal;
use Filament\Tables;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Collection;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Fieldset::make('create new user')
                    ->schema([
                        TextInput::make('name')->required()->placeholder('Name')->autofocus(),
                        TextInput::make('username')->required()->placeholder('username')->autofocus()->unique(
                            table: User::class,
                            column: 'username',
                            ignoreRecord: true,
                        ),
                        TextInput::make('email')->required()->email()->placeholder('Email')->autocomplete('email')->unique(
                            table: User::class,
                            column: 'email',
                            ignoreRecord: true,
                        ),
                        TextInput::make('password')->required(fn (Page $livewire): bool => $livewire instanceof CreateRecord)->password()->placeholder('Password')
                            ->dehydrateStateUsing(fn (String $state): string => Hash::make($state))
                            ->dehydrated(fn (?string $state): bool => filled($state)),

                        Select::make('roles')->multiple()->relationship('roles', 'name')->required()->options(Role::all()->pluck('name', 'id')),
                        TextInput::make('total_point')->disabled()
                    ]),
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Select::make('division_id')
                            ->relationship('division', 'name')

                            ->options(
                                Division::all()->pluck('name', 'id')
                            )
                            ->required(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('username')->searchable()->sortable(),
                TextColumn::make('division.name')->label('Division')->searchable(),
                TextColumn::make('email')->searchable()->sortable(),
                TextColumn::make('roles.name')->label('Role')->searchable(),

            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            UserResource\RelationManagers\MemberPointsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
