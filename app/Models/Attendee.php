<?php

namespace App\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attendee extends Model
{
    use HasFactory, SoftDeletes;

    public $table = 'attendees';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'email',
        'name',
        'emp_code',
        'comment',
        'response_status',
    ];

    public $orderable = [
        'id',
        'email',
        'emp_code',
        'name',
        'comment',
        'response_status',
    ];

    public $filterable = [
        'id',
        'email',
        'emp_code',
        'name',
        'comment',
        'response_status',
    ];

    public function absent()
    {
        return $this->belongsToMany(MeetingMinute::class, 'absent_meeting_minute');
    }

    public function owner()
    {
        return $this->belongsTo(User::class);
    }
}
