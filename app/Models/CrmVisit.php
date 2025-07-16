<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrmVisit extends Model
{
    protected $fillable = [
        'trans_no',
        'emp_id',
        'remark',
        'year',
        'month',
        'week',
    ];

    public function details()
    {
        return $this->hasMany(CrmVisitDetail::class, 'trans_no', 'trans_no');
    }
}