<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Meeting;
use Carbon\Carbon;

class MeetingInviteWithICS extends Mailable
{
    use Queueable, SerializesModels;

    public Meeting $meeting;
    protected bool $isCancel;



    protected string $mode; // create | update | cancel

    public function __construct(Meeting $meeting, string $mode = 'create')
    {
        $this->meeting = $meeting->fresh(['recurrence', 'addAttendee', 'host', 'rooms']);
        $this->mode = $mode;
    }

    public function build()
    {
        $meeting = $this->meeting;

        /*
        |--------------------------------------------------------------------------
        | Build Start & End (UTC Required for Google)
        |--------------------------------------------------------------------------
        */
        $start = Carbon::parse($meeting->date)
            ->setTimeFromTimeString($meeting->start_time);

        $end = $start->copy()->addMinutes((int) $meeting->duration);

        $startUtc = $start->copy()->setTimezone('UTC');
        $endUtc   = $end->copy()->setTimezone('UTC');

        $dtStart = $startUtc->format('Ymd\THis\Z');
        $dtEnd   = $endUtc->format('Ymd\THis\Z');
        $dtStamp = now()->setTimezone('UTC')->format('Ymd\THis\Z');

        /*
        |--------------------------------------------------------------------------
        | RRULE (Recurring Support)
        |--------------------------------------------------------------------------
        */
        $rrule = '';

        if ($meeting->recurrence) {

            $freq     = strtoupper($meeting->recurrence->frequency);
            $interval = $meeting->recurrence->interval ?? 1;

            $rrule = "RRULE:FREQ={$freq};INTERVAL={$interval}";

            if ($meeting->recurrence->occurrences) {
                $rrule .= ";COUNT={$meeting->recurrence->occurrences}";
            }

            if ($meeting->recurrence->end_date) {
                $until = Carbon::parse($meeting->recurrence->end_date)
                    ->endOfDay()
                    ->setTimezone('UTC')
                    ->format('Ymd\THis\Z');

                $rrule .= ";UNTIL={$until}";
            }

            $rrule .= "\r\n";
        }

        /*
        |--------------------------------------------------------------------------
        | Escape Text
        |--------------------------------------------------------------------------
        */
        $escape = fn($value) =>
            str_replace(['\\', ',', ';', "\n"], ['\\\\', '\,', '\;', '\n'], $value ?? '');

        $hostName  = $escape($meeting->host->name ?? '');
        $summary   = $escape($meeting->name);
        $desc      = $escape($meeting->description);
        $location  = $escape($meeting->rooms->name ?? '');

        /*
        |--------------------------------------------------------------------------
        | Attendees
        |--------------------------------------------------------------------------
        */
        $attendeeLines = '';

        foreach ($meeting->addAttendee as $attendee) {

            $name = $escape($attendee->name ?? $attendee->email);

            $attendeeLines .=
                "ATTENDEE;CN={$name};RSVP=TRUE:mailto:{$attendee->email}\r\n";
        }

        /*
        |--------------------------------------------------------------------------
        | ICS Content
        |--------------------------------------------------------------------------
        */
        $method = match ($this->mode) {
            'cancel' => 'CANCEL',
            default  => 'REQUEST',
        };

        $status = match ($this->mode) {
            'cancel' => 'CANCELLED',
            default  => 'CONFIRMED',
        };

        $ics = "BEGIN:VCALENDAR\r\n"
            . "VERSION:2.0\r\n"
            . "PRODID:-//YourCompany//MeetingScheduler//EN\r\n"
            . "CALSCALE:GREGORIAN\r\n"
            . "METHOD:{$method}\r\n"
            . "BEGIN:VEVENT\r\n"
            . "UID:{$meeting->id}@yourdomain.com\r\n"
            . "DTSTAMP:{$dtStamp}\r\n"
            . "DTSTART:{$dtStart}\r\n"
            . "DTEND:{$dtEnd}\r\n"
            . $rrule
            . "SEQUENCE:{$meeting->updated_at->timestamp}\r\n"
            . "STATUS:{$status}\r\n"
            . "SUMMARY:{$summary}\r\n"
            . "DESCRIPTION:{$desc}\r\n"
            . "LOCATION:{$location}\r\n"
            . "ORGANIZER;CN={$hostName}:mailto:{$meeting->host->email}\r\n"
            . $attendeeLines
            . "END:VEVENT\r\n"
            . "END:VCALENDAR\r\n";

        $subjectPrefix = match ($this->mode) {
            'cancel' => 'Cancelled: ',
            'update' => 'Updated: ',
            default  => 'Invitation: ',
        };

        return $this
            ->subject($subjectPrefix . $meeting->name)
            ->view('emails.meeting_invite')
            ->with(['meeting' => $meeting])
            ->attachData(
                $ics,
                'invite.ics',
                [
                    'mime' => 'text/calendar; method=' . $method . '; charset=UTF-8',
                ]
            );
    }
}
