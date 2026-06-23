<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'nik',
        'name',
        'gender',
        'religion',
        'join_date',
        'department',
        'position',
        'date_of_birth',
        'leave_balance',
    ];

    public function canteenTransactions()
    {
        return $this->hasMany(CanteenTransaction::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function activities()
    {
        return $this->hasMany(EmployeeActivity::class);
    }
}
