<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FileUploadResource\Pages;
use App\Models\FileUpload;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;

class FileUploadResource extends Resource
{
    protected static ?string $model = FileUpload::class;

    protected static ?string $navigationIcon = 'heroicon-o-paper-airplane';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('file_path')
                    ->label('File')
                    ->required(),
                Forms\Components\TextInput::make('file_name')
                    ->label('File Name')
                    ->required(),
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'accepted' => 'Accepted',
                        'rejected' => 'Rejected',
                    ])
                    ->default('pending')
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('file_name')->label('Nama File'),
                Tables\Columns\TextColumn::make('user.name')->label('Nama Pengirim'),
                Tables\Columns\TextColumn::make('status')->label('Status'),
                Tables\Columns\TextColumn::make('created_at')->label('Tanggal & Waktu')->dateTime(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFileUploads::route('/'),
            'create' => Pages\CreateFileUpload::route('/create'),
            'edit' => Pages\EditFileUpload::route('/{record}/edit'),
        ];
    }
}
