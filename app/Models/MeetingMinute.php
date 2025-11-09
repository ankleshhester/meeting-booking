<?php

namespace App\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MeetingMinute extends Model
{
    use HasFactory,  SoftDeletes ;

    public $table = 'meeting_minutes';

    protected $dates = [
        'date',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public const MEETING_MODE_SELECT = [
        'Virtual'   => 'Virtual',
        'In-Person' => 'In-Person',
    ];

    protected $fillable = [
        'name',
        'date',
        'start_time',
        'duration',
        'end_time',
        'called_by_id',
        'note_taker_id',
        'description',
        'rooms_id',
        'meeting_mode',
        'meeting_id',
    ];

    public $orderable = [
        'id',
        'name',
        'date',
        'start_time',
        'duration',
        'end_time',
        'called_by.name',
        'note_taker.name',
        'description',
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
        'present.email',
        'absent.name',
        'called_by.name',
        'note_taker.name',
        'description',
        'rooms.name',
        'meeting_mode',
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

    public function present()
    {
        return $this->belongsToMany(Attendee::class);
    }

    public function absent()
    {
        return $this->belongsToMany(Attendee::class, 'absent_meeting_minute');
    }

    public function calledBy()
    {
        return $this->belongsTo(User::class);
    }

    public function noteTaker()
    {
        return $this->belongsTo(User::class);
    }

    public function rooms()
    {
        return $this->belongsTo(ConferenceRoom::class);
    }

    public function meeting()
    {
        return $this->belongsTo(Meeting::class);
    }

    public function getMeetingModeLabelAttribute($value)
    {
        return static::MEETING_MODE_SELECT[$this->meeting_mode] ?? null;
    }

    public function getCreatedAtAttribute($value)
    {
        return $value ? Carbon::createFromFormat('Y-m-d H:i:s', $value)->format(config('project.datetime_format')) : null;
    }

    public function getUpdatedAtAttribute($value)
    {
        return $value ? Carbon::createFromFormat('Y-m-d H:i:s', $value)->format(config('project.datetime_format')) : null;
    }

    public function getDeletedAtAttribute($value)
    {
        return $value ? Carbon::createFromFormat('Y-m-d H:i:s', $value)->format(config('project.datetime_format')) : null;
    }

    public function owner()
    {
        return $this->belongsTo(User::class);
    }
}
