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
use App\Http\Controllers\CertificateController;
use App\Models\Event;
use App\Models\User;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Group;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ExportBulkAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class UsersRelationManager extends RelationManager
{
    protected static string $relationship = 'users';
    protected static ?string $title = "Daftar Hadir";

    public static function shouldSkipAuthorization(): bool
    {
        return true;
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('user_id')
                ->label('User')
                ->relationship('users', 'name')
                ->required()
                ->hiddenOn('edit'),
            Forms\Components\Checkbox::make('is_competence'),
            Forms\Components\TextInput::make('final_project_link'),
            Forms\Components\TextInput::make('submission_score'),
            Forms\Components\TextInput::make('participation_score'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('User Name'),
                Tables\Columns\TextColumn::make('username'),
                Tables\Columns\IconColumn::make('is_competence')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-circle'),
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
                Tables\Actions\AttachAction::make()
                    ->after(function (array $data) {
                        $user = User::find($data['recordId']);
                        $record = $this->getOwnerRecord();

                        $event = Event::where('id', $record->id)
                            ->where('quota', '>', 0)
                            ->lockForUpdate() // Lock baris untuk mencegah race condition
                            ->first();

                        if (!$event) {
                            throw new \Exception('Kuota event sudah habis.');
                        }

                        $certificateNumber = (new CertificateController)->generateCertificateNumber($record, $user);

                        $event->event_users()->attach($user->id, [
                            'number_certificate' => $certificateNumber,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        $record->quota -= 1;
                        $record->save();
                    }),
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
