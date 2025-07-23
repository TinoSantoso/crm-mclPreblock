<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeVisit extends Model
{
    protected $table = 'employee_visits';

    protected $fillable = [
        'area',
        'employee_id',
        'employee_name',
        'period_year',
        'period_month',
        'standard_working_days',
        'total_offline_visits',
        'total_online_visits',
        'adjustment_from_asm',
        'note_adjustment',
        'final_total_visits'
    ];

    protected $casts = [
        'period_year' => 'integer',
        'period_month' => 'integer',
        'standard_working_days' => 'integer',
        'total_offline_visits' => 'integer',
        'total_online_visits' => 'integer',
        'adjustment_from_asm' => 'integer',
        'final_total_visits' => 'integer',
    ];

    /**
     * Get visit details for this employee visit record
     */
    public function visitDetails(): HasMany
    {
        return $this->hasMany(EmployeeVisitDetail::class);
    }

    /**
     * Get offline visit details only
     */
    public function offlineVisits(): HasMany
    {
        return $this->visitDetails()->where('visit_type', 'offline');
    }

    /**
     * Get online visit details only
     */
    public function onlineVisits(): HasMany
    {
        return $this->visitDetails()->where('visit_type', 'online');
    }

    /**
     * Scope to filter by period (year and month)
     */
    public function scopeByPeriod($query, $year, $month)
    {
        return $query->where('period_year', $year)
                    ->where('period_month', $month);
    }

    /**
     * Scope to filter by area
     */
    public function scopeByArea($query, $area)
    {
        return $query->where('area', $area);
    }

    /**
     * Scope to filter by employee ID
     */
    public function scopeByEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    /**
     * Get formatted period string
     */
    public function getFormattedPeriodAttribute()
    {
        return sprintf('%04d-%02d', $this->period_year, $this->period_month);
    }

    /**
     * Get total visits (offline + online)
     */
    public function getTotalVisitsAttribute()
    {
        return $this->total_offline_visits + $this->total_online_visits;
    }

    /**
     * Get visits by day array (for display in table format)
     */
    public function getVisitsByDayArray()
    {
        $days = [];
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $this->period_month, $this->period_year);
        
        // Initialize all days to 0
        for ($i = 1; $i <= 31; $i++) {
            $days[$i] = 0;
        }
        
        // Fill in actual visit data
        $this->visitDetails->each(function ($detail) use (&$days) {
            $days[$detail->visit_day] = 1; // 1 means there was a visit (offline or online)
        });
        
        return $days;
    }

    /**
     * Recalculate totals based on visit details
     */
    public function recalculateTotals()
    {
        $this->total_offline_visits = $this->offlineVisits()->count();
        $this->total_online_visits = $this->onlineVisits()->count();
        $this->final_total_visits = $this->total_visits + $this->adjustment_from_asm;
        $this->save();
    }

    /**
     * Auto-calculate totals when model is saved
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // Auto-calculate final total if not provided
            if (empty($model->final_total_visits)) {
                $model->final_total_visits = $model->total_offline_visits + $model->total_online_visits + $model->adjustment_from_asm;
            }
        });
    }
}
