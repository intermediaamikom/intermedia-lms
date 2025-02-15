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
use App\Models\Certificate;

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
                ])->visible(fn() => User::find(auth()->user()->id)->hasRole('Super Admin'))
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable()->label('Nama Event'),
                TextColumn::make('division.name')->searchable()->sortable()->label('Divisi'),
                TextColumn::make('occasion_date')->sortable()->dateTime()->label('Tanggal Acara'),
                TextColumn::make('start_register')->sortable()->dateTime()->label('Mulai Pendaftaran'),
                TextColumn::make('end_register')->sortable()->dateTime()->label('Tutup Pendaftaran'),
                TextColumn::make('quota')->sortable()->label('Kuota'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('detail')
                    ->label('Detail')
                    ->icon('heroicon-o-eye')
                    ->color(Color::Gray)
                    ->visible(fn(Event $record) => $record->users->contains(auth()->user()))
                    ->mountUsing(fn(Forms\ComponentContainer $form, Event $record) => $form->fill([
                        'name' => $record->name,
                        'is_competence' => $record->attendances()->where('user_id', auth()->user()->id)->first()->is_competence,
                        'certificate_link' => $record->attendances()->where('user_id', auth()->user()->id)->first()->certificate_link,
                        'final_project_link' => $record->attendances()->where('user_id', auth()->user()->id)->first()->final_project_link,
                        'submission_score' => $record->attendances()->where('user_id', auth()->user()->id)->first()->submission_score,
                        'participation_score' => $record->attendances()->where('user_id', auth()->user()->id)->first()->participation_score,
                    ]))
                    ->form([
                        TextInput::make('name')->readOnly(),
                        TextInput::make('final_project_link')->url()->label('Link Final Projek'),
                        Checkbox::make('is_competence')->label('Kompeten')->disabled(),
                        TextInput::make('certificate_link')->url()->label('Link E-Sertifikat')->readOnly(),
                        TextInput::make('submission_score')->url()->label('Nilai Submission')->readOnly(),
                        TextInput::make('participation_score')->url()->label('Nilai Participation')->readOnly(),
                    ])
                    ->action(function (array $data, Event $record) {
                        $attendance = $record->attendances->where('user_id', auth()->user()->id)->first();
                        $attendance->final_project_link = $data['final_project_link'];
                        $attendance->save();
                    })
                    ->modalSubmitAction(fn(StaticAction $action) => $action->label('Submit Final Project')),
                Tables\Actions\Action::make('viewCertificateAction')
                    ->label('Sertifikat Kehadiran')
                    ->icon('heroicon-o-document-text')
                    ->visible(fn(Event $record) => $record->users->contains(auth()->user()) && Carbon::now()->gt(Carbon::parse($record->occasion_date)))
                    ->form([
                        TextInput::make('full_name')
                            ->label('Nama Lengkap')
                            ->default(auth()->user()->name)
                            ->disabled()
                            ->required(),
                        TextInput::make('event_name')
                            ->label('Nama Acara')
                            ->default(fn(Event $record) => $record->name)
                            ->disabled()
                            ->required(),
                        TextInput::make('event_date')
                            ->label('Tanggal Acara')
                            ->default(fn(Event $record) => Carbon::parse($record->occasion_date)->format('d-m-Y'))
                            ->disabled()
                            ->required(),
                    ])
                    ->action(function (array $data, Event $record) {
                        $user_id = auth()->user()->id;
                        $event_id = $record->id;
                        $full_name = auth()->user()->name;
                        $event_name = $record->name;
                        $event_date = Carbon::parse($record->occasion_date)->format('d-m-Y');

                        Certificate::create([
                            'user_id' => $user_id,
                            'event_id' => $event_id,
                            'full_name' => $full_name,
                            'event_name' => $event_name,
                            'event_occasion_date' => $event_date,
                        ]);

                        $pdfUrl = url("laporan_kehadiran.php?user_id=$user_id&event_id=$event_id");
                        echo "<script>
                            var printWindow = window.open('$pdfUrl', '_blank');
                            printWindow.onload = function() {
                                printWindow.print();
                            };
                        </script>";
                    })
                    ->modalSubmitAction(fn(StaticAction $action) => $action->label('Download Sertifikat')),
                Tables\Actions\Action::make('joinEventAction')
                    ->label('Join')
                    ->icon('heroicon-o-arrow-left-end-on-rectangle')
                    ->disabled(function (Event $record) {
                        $carbonDate = Carbon::parse($record->occasion_date);
                        return $record->quota == 0 || (Carbon::now()->lt(Carbon::parse($record->start_register)) ||
                            Carbon::now()->gt(Carbon::parse($record->end_register))) ||
                            User::find(auth()->user()->id)->events()
                            ->whereYear('occasion_date', $carbonDate->year)
                            ->whereMonth('occasion_date', $carbonDate->month)
                            ->whereDay('occasion_date', $carbonDate->day)->count() > 0;
                    })
                    ->visible(fn(Event $record) => !$record->users->contains(auth()->user()))
                    ->mountUsing(fn(Forms\ComponentContainer $form, Event $record) => $form->fill([
                        'name' => $record->name,
                        'description' => $record->description,
                        'occasion_date' => $record->occasion_date,
                        'quota' => $record->quota,
                        'user_id' => Auth::user()->id
                    ]))
                    ->form([
                        Select::make('user_id')
                            ->label('Join Sebagai')
                            ->options(User::query()->pluck('name', 'id')),
                        TextInput::make('name')->label('Nama Event'),
                        Textarea::make('description')->label('Deskripsi'),
                        TextInput::make('occasion_date')->label('Tanggal Acara')
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
                    ->modalSubmitAction(fn(StaticAction $action) => $action->label('Join Event')),
                Tables\Actions\Action::make('cancelJoinEventAction')
                    ->label('Batal Join')
                    ->icon('heroicon-o-arrow-left-end-on-rectangle')
                    ->color(Color::Amber)
                    ->visible(fn(Event $record) => $record->users->contains(auth()->user()))
                    ->mountUsing(fn(Forms\ComponentContainer $form, Event $record) => $form->fill([
                        'name' => $record->name,
                        'description' => $record->description,
                        'occasion_date' => $record->occasion_date,
                        'quota' => $record->quota,
                        'user_id' => Auth::user()->id
                    ]))
                    ->form([
                        Select::make('user_id')
                            ->label('Sebagai')
                            ->options(User::query()->pluck('name', 'id')),
                        TextInput::make('name')->label('Nama Event'),
                        Textarea::make('description')->label('Deskripsi'),
                        TextInput::make('occasion_date')->label('Tanggal Acara')
                    ])
                    ->action(function (array $data, Event $record) {
                        $record->users()->detach($data['user_id']);

                        $record->quota += 1;
                        $record->save();
                    })
                    ->disabledForm()
                    ->modalAlignment(Alignment::Center)
                    ->modalSubmitAction(fn(StaticAction $action) => $action->label('Batal Join')),
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
}
