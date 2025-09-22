<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'operator_id',
        'date',
        'status',
        'tenant_id',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /**
     * Disable timestamps for better write performance if not needed
     * Uncomment the line below if timestamps are not critical
     */
    // public $timestamps = false;

    /**
     * Optimize bulk operations
     */
    public static function createBulk(array $attendances): bool
    {
        try {
            return static::insert($attendances);
        } catch (\Exception $e) {
            \Log::error('Bulk attendance creation failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Optimized upsert for attendance records
     */
    public static function upsertAttendance(int $operatorId, string $date, string $status, int $tenantId): bool
    {
        try {
            return static::updateOrCreate(
                [
                    'operator_id' => $operatorId,
                    'date' => $date,
                    'tenant_id' => $tenantId
                ],
                [
                    'status' => $status
                ]
            ) !== null;
        } catch (\Exception $e) {
            \Log::error('Attendance upsert failed: ' . $e->getMessage());
            return false;
        }
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(Operator::class);
    }

    public function scopeForToday($query)
    {
        return $query->whereDate('date', today());
    }

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('date', $date);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }
} 