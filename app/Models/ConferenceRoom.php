<?php

namespace App\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConferenceRoom extends Model
{
    use HasFactory, SoftDeletes;

    public $table = 'conference_rooms';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'building_area_id',
        'name',
        'capacity',
        'description',
    ];

    public $orderable = [
        'id',
        'building_area.name',
        'name',
        'capacity',
        'description',
    ];

    public $filterable = [
        'id',
        'building_area.name',
        'name',
        'capacity',
        'description',
    ];

    public function buildingArea()
    {
        return $this->belongsTo(BuildingsArea::class);
    }

    public function meeting()
    {
        return $this->hasMany(Meeting::class, 'rooms_id', 'id');
    }

    public function owner()
    {
        return $this->belongsTo(User::class);
    }
}
