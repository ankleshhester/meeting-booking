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

        $startDateTime = Carbon::parse($meeting->date); // Start with the correct meeting date
        $startTime = Carbon::parse($meeting->start_time); // Parse the time component

        $startDateTime->setTime($startTime->hour, $startTime->minute, $startTime->second);

        $start = $startDateTime->format('Ymd\THis');

        $endTime = Carbon::parse($meeting->end_time);
        $endDateTime = Carbon::parse($meeting->date); // Start with the correct meeting date
        $endDateTime->setTime($endTime->hour, $endTime->minute, $endTime->second);

        $end = $endDateTime->format('Ymd\THis');

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
