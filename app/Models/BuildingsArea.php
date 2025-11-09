<?php

namespace App\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BuildingsArea extends Model
{
    use HasFactory, SoftDeletes;

    public $table = 'buildings_areas';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'name',
        'description',
        'floors',
        'address',
    ];

    public $orderable = [
        'id',
        'name',
        'description',
        'floors',
        'address',
    ];

    public $filterable = [
        'id',
        'name',
        'description',
        'floors',
        'address',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class);
    }
}
