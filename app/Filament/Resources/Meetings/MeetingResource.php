<?php

namespace App\Filament\Resources\Meetings;

use App\Filament\Resources\Meetings\Pages\CreateMeeting;
use App\Filament\Resources\Meetings\Pages\EditMeeting;
use App\Filament\Resources\Meetings\Pages\ListMeetings;
use App\Filament\Resources\Meetings\Schemas\MeetingForm;
use App\Filament\Resources\Meetings\Tables\MeetingsTable;
use App\Models\Meeting;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Attendee;
use App\Models\MeetingMinute;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\MeetingInviteWithICS;
use Illuminate\Support\Facades\Log;
use Filament\Forms;

class MeetingResource extends Resource
{
    protected static ?string $model = Meeting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::CalendarDays;

    protected static ?int $navigationSort = 100;

    protected static ?string $recordTitleAttribute = 'Meeting';

    public static function form(Schema $schema): Schema
    {
        return MeetingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MeetingsTable::configure($table);
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
            'index' => ListMeetings::route('/'),
            'create' => CreateMeeting::route('/create'),
            'edit' => EditMeeting::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $user = Auth::user();

        $query = parent::getEloquentQuery();

        // If the logged-in user is admin, show all meetings
        if ($user->hasRole('super_admin')) {
            return $query;
        }

        return $query
            ->where(function ($q) use ($user) {
                $q->where('created_by_id', $user->id) // Meetings created (hosted) by logged-in user

                // Or meetings where the attendee email matches the logged-in user's email
                ->orWhereHas('addAttendee', function ($q2) use ($user) {
                    $q2->where('email', $user->email);
                });
            });
    }

}
