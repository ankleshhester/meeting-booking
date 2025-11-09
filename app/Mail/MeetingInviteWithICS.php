<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Meeting;
use Carbon\Carbon;

class MeetingInviteWithICS extends Mailable
{
    use Queueable, SerializesModels;

    public $meeting;

    /**
     * Create a new message instance.
     */
    public function __construct(Meeting $meeting)
    {
        $this->meeting = $meeting;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Meeting Invitation: ' . $this->meeting->name,
        );
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $meeting = $this->meeting;

        // 🛑 FIX: Correctly parse the date and time to prevent "Double date specification" error
        // 1. Get the date component (YYYY-MM-DD)
        $startDateTime = Carbon::parse($meeting->date);

        // 2. Get the time component (HH:MM:SS)
        $startTime = Carbon::parse($meeting->start_time);

        // 3. Combine them: set the hour/minute/second of the date to the time component
        $startDateTime->setTime($startTime->hour, $startTime->minute, $startTime->second);

        // Format DTSTART for the ICS file
        $dtStart = $startDateTime->format('Ymd\THis');

        // Calculate DTEND by adding duration to DTSTART
        $endDateTime = $startDateTime->copy()->addMinutes($meeting->duration);
        $dtEnd = $endDateTime->format('Ymd\THis');

        // Format DTSTAMP
        $dtStamp = now()->setTimezone('UTC')->format('Ymd\THis\Z');

        $hostName = str_replace(['\\', ',', ';'], ['\\\\', '\,', '\;'], $meeting->host->name);

        // 🔑 FIX: Generate the attendee list using standard PHP concatenation
        $attendeeList = '';

        // Add Host as an explicit attendee
        $attendeeList .= "ATTENDEE;CN={$hostName};RSVP=TRUE:mailto:{$meeting->host->email}\r\n";

        // Add other attendees
        foreach ($meeting->addAttendee as $attendee) {
            $name = str_replace(['\\', ',', ';'], ['\\\\', '\,', '\;'], $attendee->name ?? $attendee->email);
            $attendeeList .= "ATTENDEE;CN={$name};RSVP=TRUE:mailto:{$attendee->email}\r\n";
        }

        // Build the ICS content string
        $icsContent = "BEGIN:VCALENDAR\r\n"
            . "VERSION:2.0\r\n"
            . "PRODID:-//Your Company//Meeting Scheduler//EN\r\n"
            . "CALSCALE:GREGORIAN\r\n"
            . "METHOD:REQUEST\r\n"
            . "BEGIN:VEVENT\r\n"
            . "DTSTART:{$dtStart}\r\n"
            . "DTEND:{$dtEnd}\r\n"
            . "DTSTAMP:{$dtStamp}\r\n"
            . "UID:{$meeting->id}@yourdomain.com\r\n"
            . "CREATED:{$meeting->created_at->format('Ymd\THis\Z')}\r\n"
            . "DESCRIPTION:{$meeting->description}\r\n"
            . "LAST-MODIFIED:{$dtStamp}\r\n"
            . "LOCATION:{$meeting->rooms->name}\r\n"
            . "SEQUENCE:0\r\n"
            . "STATUS:CONFIRMED\r\n"
            . "SUMMARY:{$meeting->name}\r\n"
            . "TRANSP:OPAQUE\r\n"
            . "ORGANIZER;CN={$hostName}:mailto:{$meeting->host->email}\r\n"
            . $attendeeList // Inject the PHP-generated attendee list
            . "END:VEVENT\r\n"
            . "END:VCALENDAR\r\n";

        return $this
            ->view('emails.meeting_invite') // Your Blade view for the email body
            ->with([
                'meeting' => $meeting,
            ])
            ->attachData(
                $icsContent,
                'invite.ics',
                [
                    'mime' => 'text/calendar', // Crucial for automatic calendar detection
                ]
            );
    }
}
