<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'date',
        'tap_in',
        'tap_out',
        'status',
        'overtime_hours',
        'is_manual',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
