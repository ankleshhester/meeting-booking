<p>Hello,</p>

<p>You are invited to the meeting:</p>

<p><strong>{{ $meeting->name }}</strong></p>
<p>Date: {{ $meeting->date }}<br>
Start: {{ $meeting->start_time }}<br>
End: {{ $meeting->end_time }}</p>

<p>Location: {{ $meeting->rooms->name }}</p>

<p>Attached calendar invite has been included.</p>
