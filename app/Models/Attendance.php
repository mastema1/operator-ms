<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'operator_id',
        'date',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function operator(): BelongsTo
    {
        return $this->belongsTo(Operator::class);
    }

    public function scopeForToday($query)
    {
        return $query->whereDate('date', today());
    }
} 