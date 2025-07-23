<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeVisitDetail extends Model
{
    protected $table = 'employee_visit_details';

    protected $fillable = [
        'employee_visit_id',
        'visit_day',
        'visit_type',
        'client_name',
        'visit_notes',
        'visit_datetime'
    ];

    protected $casts = [
        'visit_day' => 'integer',
        'visit_datetime' => 'datetime',
    ];

    /**
     * Get the parent employee visit record
     */
    public function employeeVisit(): BelongsTo
    {
        return $this->belongsTo(EmployeeVisit::class);
    }

    /**
     * Scope for offline visits
     */
    public function scopeOffline($query)
    {
        return $query->where('visit_type', 'offline');
    }

    /**
     * Scope for online visits
     */
    public function scopeOnline($query)
    {
        return $query->where('visit_type', 'online');
    }

    /**
     * Scope for specific day
     */
    public function scopeByDay($query, $day)
    {
        return $query->where('visit_day', $day);
    }

    /**
     * Check if visit is offline
     */
    public function isOffline()
    {
        return $this->visit_type === 'offline';
    }

    /**
     * Check if visit is online
     */
    public function isOnline()
    {
        return $this->visit_type === 'online';
    }

    /**
     * Auto-update parent totals when visit detail changes
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function ($model) {
            $model->employeeVisit->recalculateTotals();
        });

        static::deleted(function ($model) {
            $model->employeeVisit->recalculateTotals();
        });
    }
}