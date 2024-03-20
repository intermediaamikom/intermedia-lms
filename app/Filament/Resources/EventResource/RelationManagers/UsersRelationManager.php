<?php

namespace App\Filament\Resources\EventResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Exports\EventAttendancesExporter;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ExportBulkAction;

class UsersRelationManager extends RelationManager
{
    protected static string $relationship = 'users';
    protected static ?string $title = "Attendances";

    public static function shouldSkipAuthorization(): bool
    {
        return true;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Checkbox::make('is_competence'),
                Forms\Components\TextInput::make('certificate_link'),
                Forms\Components\TextInput::make('submission_score'),
                Forms\Components\TextInput::make('participation_score'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\IconColumn::make('is_competence')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-circle'),
                Tables\Columns\TextColumn::make('certificate_link'),
                Tables\Columns\TextColumn::make('final_project_link')
                    ->copyable()
                    ->copyMessage('Final project copied')
                    ->copyMessageDuration(1500),
                Tables\Columns\TextColumn::make('submission_score'),
                Tables\Columns\TextColumn::make('participation_score'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make(),
                ExportAction::make()
                    ->exporter(EventAttendancesExporter::class)
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                    ExportBulkAction::make()
                        ->exporter(EventAttendancesExporter::class)
                ]),
            ]);
    }
}
