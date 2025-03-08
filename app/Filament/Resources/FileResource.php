<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FileResource\Pages;
use App\Filament\Resources\FileResource\RelationManagers;
use App\Models\File;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class FileResource extends Resource
{
    protected static ?string $model = File::class;

    protected static ?string $navigationIcon = 'heroicon-o-document';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('file_path')
                    ->label('Upload File')
                    ->acceptedFileTypes(['application/pdf'])
                    ->directory('uploads')
                    ->maxSize(102400)
                    ->required(),
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Pending',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ])
                            ->default('pending')
                            ->required()
                            ->visible(fn() => User::find(Auth::id())->hasRole(['Super Admin', 'Admin'])),
                        Forms\Components\TextInput::make('points')
                            ->label('Points')
                            ->numeric()
                            ->default(0)
                            ->required()
                            ->visible(fn() => User::find(Auth::id())->hasRole(['Super Admin', 'Admin'])),
                    ]),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('file_path')
                    ->label('File')
                    ->circular()
                    ->size(50)
                    ->getStateUsing(function ($record) {
                        if (str_contains($record->file_path, 'pdf')) {
                            return 'https://assets.monica.im/tools-web/_next/static/media/pdfLogo.d3b0a44c.png';
                        }
                        return Storage::url($record->file_path);
                    })
                    ->url(function ($record) {
                        return Storage::url($record->file_path);
                    })
                    ->openUrlInNewTab(),
                Tables\Columns\TextColumn::make('user.username')->label('NIM'),
                Tables\Columns\TextColumn::make('user.name')->label('Member'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn($state) => ucfirst($state)),
                Tables\Columns\TextColumn::make('points')->label('Points'),
                Tables\Columns\TextColumn::make('created_at')->label('Uploaded At')->dateTime()->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->form([
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Pending',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ])
                            ->default('pending')
                            ->required()
                            ->visible(fn() => User::find(Auth::id())->hasRole(['Super Admin', 'Admin'])),
                        Forms\Components\TextInput::make('points')
                            ->label('Points')
                            ->numeric()
                            ->default(0)
                            ->required()
                            ->visible(fn() => User::find(Auth::id())->hasRole(['Super Admin', 'Admin'])),
                    ])
                    ->action(function (File $record, array $data) {
                        $record->update($data);
                        return redirect()->route('filament.resources.files.index');
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListFiles::route('/'),
            'create' => Pages\CreateFile::route('/create'),
            'edit' => Pages\EditFile::route('/{record}/edit'),
        ];
    }
}
