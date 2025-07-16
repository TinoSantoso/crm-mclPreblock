<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrmVisitDetail extends Model
{
    protected $fillable = [
        'trans_no',
        'account',
        'contact',
        'visit_date',
        'is_visited',
    ];

    public function visit()
    {
        return $this->belongsTo(CrmVisit::class, 'trans_no', 'trans_no');
    }
}