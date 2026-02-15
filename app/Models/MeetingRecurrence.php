<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class MeetingRecurrence extends Model
{
    use HasFactory;

    protected $table = 'meeting_recurrences';

    protected $fillable = [
        'meeting_id',
        'frequency',        // daily | weekly | monthly
        'interval',         // every N units
        'days_of_week',     // ["mon","wed"]
        'start_date',
        'end_date',
        'occurrences',
    ];

    protected $casts = [
        'days_of_week' => 'array',
        'start_date'   => 'date',
        'end_date'     => 'date',
    ];

    /* =======================
     | Relationships
     =======================*/

    public function meeting()
    {
        return $this->belongsTo(Meeting::class);
    }

    /* =======================
     | Helpers
     =======================*/

    public function isWeekly(): bool
    {
        return $this->frequency === 'weekly';
    }

    public function hasEndCondition(): bool
    {
        return $this->end_date || $this->occurrences;
    }

    public function getCarbonDaysOfWeek(): array
    {
        return collect($this->days_of_week ?? [])
            ->map(fn ($day) => Carbon::parse($day)->dayOfWeek)
            ->toArray();
    }
}
