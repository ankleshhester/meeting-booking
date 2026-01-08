<?php

namespace App\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeCostMaster extends Model
{
    use HasFactory, SoftDeletes;

    public $table = 'employee_cost_masters';

    protected $fillable = [
        'emp_code',
        'email',
        'cost_per_hour',
    ];

    public $orderable = [
        'id',
        'emp_code',
        'cost_per_hour',
    ];

    public $filterable = [
        'id',
        'emp_code',
        'cost_per_hour',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];


    public function owner()
    {
        return $this->belongsTo(User::class);
    }
}
