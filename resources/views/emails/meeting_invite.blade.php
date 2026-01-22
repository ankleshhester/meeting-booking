@php
    use Carbon\Carbon;

    $startDateTime = Carbon::parse($meeting->date)
        ->setTimeFrom(Carbon::parse($meeting->start_time));

    $endDateTime = Carbon::parse($meeting->date)
        ->setTimeFrom(Carbon::parse($meeting->end_time));
@endphp

<p>Hello,</p>

<p>You are invited to the meeting:</p>

<p>
    Date: {{ $startDateTime->format('d-m-Y') }}<br>
    Start: {{ $startDateTime->format('d-m-Y H:i:s') }}<br>
    End: {{ $endDateTime->format('d-m-Y H:i:s') }}
</p>

<p>Organizer: {{ $meeting->host->name }} ({{ $meeting->host->email }})</p>

<p>Location: {{ $meeting->rooms->name }}</p>

<p>Attached calendar invite has been included.</p>
