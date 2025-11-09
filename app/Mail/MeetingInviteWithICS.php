<?php

namespace App\Mail;

use App\Models\Meeting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class MeetingInviteWithICS extends Mailable
{
    use Queueable, SerializesModels;

    public Meeting $meeting;

    public function __construct(Meeting $meeting)
    {
        $this->meeting = $meeting;
    }

    public function build()
    {
        $meeting = $this->meeting;

        $start = Carbon::parse($meeting->date . ' ' . $meeting->start_time)->format('Ymd\THis');
        $end   = Carbon::parse($meeting->date . ' ' . $meeting->end_time)->format('Ymd\THis');

        $ics = "BEGIN:VCALENDAR
            VERSION:2.0
            PRODID:-//Hester//Meeting Calendar//EN
            CALSCALE:GREGORIAN
            METHOD:REQUEST
            BEGIN:VEVENT
            UID:" . uniqid() . "
            DTSTAMP:{$start}
            DTSTART:{$start}
            DTEND:{$end}
            SUMMARY:{$meeting->name}
            DESCRIPTION:" . addslashes($meeting->description ?? '') . "
            LOCATION:" . ($meeting->room?->name ?? 'Online') . "
            ORGANIZER;CN=" . $meeting->host?->name . ":mailto:" . $meeting->host?->email . "
            END:VEVENT
            END:VCALENDAR";

        return $this->subject('Meeting Invitation: ' . $meeting->name)
            ->view('emails.meeting_invite')
            ->attachData($ics, 'meeting.ics', [
                'mime' => 'text/calendar; charset=utf-8; method=REQUEST',
            ]);
    }
}
