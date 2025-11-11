<?php

namespace App\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Meeting extends Model
{
    use HasFactory, SoftDeletes;

    public $table = 'meetings';

    protected $casts = [
        'add_meet_link' => 'boolean',
        'start_time' => 'datetime',
        'end_time'   => 'datetime',
        'attachments' => 'array',
    ];

    protected $dates = [
        'date',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $times = [
        'start_time',
        'end_time',
    ];

    public const MEETING_MODE_SELECT = [
        'Virtual'   => 'Virtual',
        'In-Person' => 'In-Person',
    ];

    public $orderable = [
        'id',
        'name',
        'date',
        'start_time',
        'duration',
        'end_time',
        'description',
        'add_meet_link',
        'rooms.name',
        'meeting_mode',
    ];

    public $filterable = [
        'id',
        'name',
        'date',
        'start_time',
        'duration',
        'end_time',
        'description',
        'add_attendee.email',
        'rooms.name',
        'meeting_mode',
    ];

    protected $fillable = [
        'name',
        'date',
        'start_time',
        'duration',
        'end_time',
        'description',
        'add_meet_link',
        'rooms_id',
        'meeting_mode',
        'created_by_id',
    ];

    public const DURATION_SELECT = [
        '15'  => '15 mins',
        '30'  => '30 mins',
        '45'  => '45 mins',
        '60'  => '1 hours',
        '90'  => '1.5 hour',
        '120' => '2 hours',
        '180' => '3 hours',
        '240' => '4 hours',
        '300' => '5 hours',
        '360' => '6 hours',
        '420' => '7 hours',
        '480' => '8 hours',
    ];


    public function getDurationLabelAttribute($value)
    {
        return static::DURATION_SELECT[$this->duration] ?? null;
    }

    public function host()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function addAttendee()
    {
        return $this->belongsToMany(Attendee::class);
    }

    public function rooms()
    {
        return $this->belongsTo(ConferenceRoom::class);
    }

    public function meetingMinutes()
    {
        return $this->belongsTo(MeetingMinute::class, 'id', 'meeting_id');
    }

    public function getMeetingModeLabelAttribute($value)
    {
        return static::MEETING_MODE_SELECT[$this->meeting_mode] ?? null;
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class);
    }
}
