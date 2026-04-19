<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CanteenTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'transaction_date',
        'tap_count',
        'total_amount',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
