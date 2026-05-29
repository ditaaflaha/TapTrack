<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'activity_type',
        'scanned_at',
        'is_manual',
        'notes',
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
        'is_manual' => 'boolean',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
