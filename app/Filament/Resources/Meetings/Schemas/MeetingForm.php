<?php

namespace App\Filament\Resources\Meetings\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Forms;
use App\Models\Meeting;
use Illuminate\Support\Facades\Auth;
use App\Models\ConferenceRoom;
use Filament\Notifications\Notification;
use Carbon\Carbon;
use Filament\Forms\Components\TagsInput;
use App\Models\Attendee;
use App\Models\User;
use App\Models\MeetingMinute;
use Illuminate\Support\Facades\Mail;
use App\Mail\MeetingInvitation;
use Filament\Forms\FormsComponent;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Group;
use App\Mail\MeetingInviteWithICS;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Section;

class MeetingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('name')
                    ->label('Meeting Title')
                    ->columnSpanFull()
                    ->required(),

                Group::make()
                ->schema([
                    Forms\Components\DatePicker::make('date')
                        ->default(Carbon::today())
                        ->reactive()
                        ->afterStateUpdated(fn ($state, callable $set, callable $get) =>
                            self::updateEndTimeAndRooms($set, $get)
                        )
                        ->required(),

                    Forms\Components\TimePicker::make('start_time')
                        //Start time rounded to next 15 minutes i.e. 10:07 AM -> 10:15 AM, 10:16 AM -> 10:30 AM
                        ->default(function () {
                            $now = Carbon::now();
                            $minutes = (int) $now->format('i');
                            $roundedMinutes = ceil($minutes / 15) * 15;
                            if ($roundedMinutes == 60) {
                                $now->addHour()->setMinute(0);
                            } else {
                                $now->setMinute($roundedMinutes);
                            }
                            return $now->format('H:i');
                        })
                        ->seconds(false)
                        ->reactive()
                        ->afterStateUpdated(fn ($state, callable $set, callable $get) =>
                            self::updateEndTimeAndRooms($set, $get)
                        )
                        ->required(),

                    Forms\Components\Select::make('duration')
                        ->options(Meeting::DURATION_SELECT)
                        ->default(30)
                        ->reactive()
                        ->afterStateUpdated(fn ($state, callable $set, callable $get) =>
                            self::updateEndTimeAndRooms($set, $get)
                        )
                        ->required(),

                    Forms\Components\TimePicker::make('end_time')
                        ->seconds(false)
                        ->default(function (callable $get) {
                            try {
                                $start = Carbon::parse($get('start_time'));
                                $duration = (int) $get('duration');
                                return $start->copy()->addMinutes($duration);
                            } catch (\Exception $e) {
                                return null;
                            }
                        })
                        ->readOnly()
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                            $start = Carbon::parse($get('start_time'));
                            $end   = Carbon::parse($state);

                            if ($end->lessThanOrEqualTo($start)) {
                                $set('end_time', null);
                                    Notification::make()
                                    ->danger()
                                    ->title('End time must be greater than start time.')
                                    ->send();
                            } else {
                                self::updateAvailableRooms($set, $get);
                            }
                        })
                        ->required(),

                    Toggle::make('is_recurring')
                        ->label('Repeat meeting')
                        ->reactive()
                        ->default(fn ($record) => $record?->recurrence()->exists())
                        ->disabled(fn ($record) => $record && ! $record->is_master)
                        ->helperText(fn ($record) =>
                            $record && ! $record->is_master
                                ? 'This meeting is part of a recurring series. Edit the master meeting to change recurrence.'
                                : null
                        )
                        ->afterStateHydrated(function ($state, callable $set, $record) {
                            if ($record && $record->recurrence) {
                                $set('is_recurring', true);

                                $set('recurrence.frequency', $record->recurrence->frequency);
                                $set('recurrence.interval', $record->recurrence->interval);
                                $set('recurrence.days_of_week', $record->recurrence->days_of_week);
                                $set('recurrence.start_date', $record->recurrence->start_date);
                                $set('recurrence.end_date', $record->recurrence->end_date);
                                $set('recurrence.occurrences', $record->recurrence->occurrences);
                            }
                        })
                        ->afterStateUpdated(function ($state, callable $set) {
                            if (! $state) {
                                $set('recurrence.frequency', null);
                                $set('recurrence.interval', null);
                                $set('recurrence.days_of_week', []);
                                $set('recurrence.start_date', null);
                                $set('recurrence.end_date', null);
                                $set('recurrence.occurrences', null);
                                $set('is_master', $state ? true : false);
                            }
                        })
                        ->afterStateHydrated(function ($state, callable $set, $record) {

                            if (! $record) {
                                return;
                            }

                            if ($record->recurrence) {
                                $set('is_recurring', true);

                                $set('recurrence.frequency', $record->recurrence->frequency);
                                $set('recurrence.interval', $record->recurrence->interval);
                                $set('recurrence.days_of_week', $record->recurrence->days_of_week);
                                $set('recurrence.start_date', $record->recurrence->start_date);
                                $set('recurrence.end_date', $record->recurrence->end_date);
                                $set('recurrence.occurrences', $record->recurrence->occurrences);
                            }
                        }),

                    Hidden::make('is_master')
                        ->default(fn ($get) => $get('is_recurring') ? true : false)
                        ->dehydrated(true),


                    // Forms\Components\Select::make('meeting_mode')
                    //     ->options(Meeting::MEETING_MODE_SELECT)
                    //     ->default('In-Person')
                    //     ->required(),
                ])
                ->columns(5)
                ->columnSpanFull(),

                        Group::make()
                            ->schema([
                                Forms\Components\Select::make('recurrence.frequency')
                                    ->label('Frequency')
                                    ->options([
                                        'daily'   => 'Daily',
                                        'weekly'  => 'Weekly',
                                        'monthly' => 'Monthly',
                                    ])
                                    ->reactive()
                                    ->visible(fn ($get, $record) =>
                                        $get('is_recurring') && (! $record || $record->is_master)
                                    )
                                    ->required(),

                                TextInput::make('recurrence.interval')
                                    ->label('Repeat every')
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(1)
                                    ->visible(fn ($get, $record) =>
                                        $get('is_recurring') && (! $record || $record->is_master)
                                    ),

                                DatePicker::make('recurrence.start_date')
                                    ->label('Start date')
                                    ->default(fn ($get) => $get('date'))
                                    ->visible(fn ($get, $record) =>
                                        $get('is_recurring') && (! $record || $record->is_master)
                                    ),

                                DatePicker::make('recurrence.end_date')
                                    ->label('End date')
                                    ->visible(fn ($get, $record) =>
                                        $get('is_recurring') && (! $record || $record->is_master)
                                    ),

                                TextInput::make('recurrence.occurrences')
                                    ->label('Max occurrences')
                                    ->numeric()
                                    ->visible(fn ($get, $record) =>
                                        $get('is_recurring') && (! $record || $record->is_master)
                                    ),

                                Forms\Components\CheckboxList::make('recurrence.days_of_week')
                                    ->label('Days of week')
                                    ->options([
                                        'mon' => 'Mon',
                                        'tue' => 'Tue',
                                        'wed' => 'Wed',
                                        'thu' => 'Thu',
                                        'fri' => 'Fri',
                                        'sat' => 'Sat',
                                        'sun' => 'Sun',
                                    ])
                                    ->reactive()
                                    ->columns(7)
                                    ->columnSpanFull()
                                    ->visible(fn ($get, $record) =>
                                        $get('is_recurring')
                                        && $get('recurrence.frequency') === 'weekly'
                                        && (! $record || $record->is_master)
                                    )
                            ])
                            ->columnSpanFull()
                            ->columns(5),


                Group::make()
                ->schema([

                    Forms\Components\Select::make('created_by_id')
                        ->relationship('createdBy', 'name') // or 'email', whichever you prefer
                        ->label('Organizer')
                        ->searchable()
                        ->preload()
                        ->default(fn () => Auth::id())
                        ->disabled()
                        ->required(),

                    Forms\Components\Select::make('add_attendee')
                        ->label('Attendees Emails')
                        ->multiple()
                        ->relationship('addAttendee', 'email')
                        ->searchable()
                        ->preload()
                        ->placeholder('Enter attendee emails and press enter'),

                    Forms\Components\Select::make('rooms_id')
                        ->label('Conference Room')
                        ->relationship('rooms', 'name') // ✅ THIS IS THE KEY
                        ->searchable()
                        ->preload()
                        ->required()
                        ->options(fn (callable $get) => self::getAvailableRooms($get)),

                ])
                ->columns(3)
                ->columnSpanFull(),

                    Forms\Components\Textarea::make('description')
                        ->label('Meeting Description')
                        ->required()
                        ->columnSpanFull(),

                    FileUpload::make('attachments')
                        ->label('Attachments')
                        ->multiple()
                        ->downloadable()
                        ->openable()
                        ->default(fn ($record) => $record?->attachments)
                        ->directory('meeting_attachments')
                        ->preserveFilenames(),

                    // Forms\Components\Toggle::make('add_meet_link')
                    //     ->label('Generate Meet Link?')
                    //     ->default(false),

                    Hidden::make('created_by_id')
                        ->default(fn () => Auth::id()),

                ]);
            }

    public static function updateEndTimeAndRooms(callable $set, callable $get): void
    {
        try {
            $start = Carbon::parse($get('start_time'));
            $duration = (int) $get('duration');
            $set('end_time', $start->copy()->addMinutes($duration)->format('H:i'));
        } catch (\Exception $e) {}

        self::updateAvailableRooms($set, $get);
    }

    public static function updateAvailableRooms(callable $set, callable $get): void
    {
        $selectedRoomId = $get('rooms_id');

        // No room selected yet → nothing to validate
        if (! $selectedRoomId) {
            return;
        }

        $availableRooms = self::getAvailableRooms($get);

        // If selected room is NOT in available list → conflict
        if (! array_key_exists($selectedRoomId, $availableRooms)) {
            $set('rooms_id', null);

            Notification::make()
                ->danger()
                ->title('Conference Room Unavailable')
                ->body('The selected room is not available for the updated time slot. Please choose another room.')
                ->persistent()
                ->send();
        }
    }

    public static function getAvailableRooms(callable $get): array
    {
        if (! $get('date') || ! $get('start_time') || ! $get('end_time')) {
            return ConferenceRoom::pluck('name', 'id')->toArray();
        }

        $date  = Carbon::parse($get('date'))->toDateString();
        $start = Carbon::parse($get('start_time'))->toTimeString();
        $end   = Carbon::parse($get('end_time'))->toTimeString();

        // 👇 CURRENT MEETING ID (NULL on create, set on edit)
        $currentMeetingId = $get('id');

        return ConferenceRoom::query()
            ->whereDoesntHave('meeting', function ($query) use ($date, $start, $end, $currentMeetingId) {
                $query
                    ->whereDate('date', $date)
                    ->where(function ($q) use ($start, $end) {
                        $q->whereTime('start_time', '<', $end)
                        ->whereTime('end_time', '>', $start);
                    })
                    // ✅ EXCLUDE CURRENT MEETING
                    ->when($currentMeetingId, function ($q) use ($currentMeetingId) {
                        $q->where('meetings.id', '!=', $currentMeetingId);
                    });
            })
            ->pluck('name', 'id')
            ->toArray();
    }





}
