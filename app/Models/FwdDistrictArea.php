<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FwdDistrictArea extends Model
{
    protected $table = 'fwd_district_areas';
    
    protected $fillable = [
        'region_id',
        'region_name',
        'area_name',
        'area_code',
        'region_code',
        'district_seq',
        'user_last_update',
        'updated_by_name'
    ];

    protected $casts = [
        'region_id' => 'integer',
        'area_code' => 'integer',
        'region_code' => 'integer',
        'district_seq' => 'integer',
        'user_last_update' => 'integer'
    ];

    /**
     * Get all areas ordered by area name
     */
    public static function getAllAreas()
    {
        return self::orderBy('area_name')->get();
    }

    /**
     * Get areas formatted for select box
     */
    public static function getAreasForSelect()
    {
        return self::orderBy('area_name')
            ->get()
            ->map(function ($area) {
                return [
                    'text' => $area->area_name,
                    'value' => $area->area_name
                ];
            })
            ->toArray();
    }
}
