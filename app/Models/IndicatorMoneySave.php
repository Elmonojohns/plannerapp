<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IndicatorMoneySave extends Model
{
    protected  $guarded = [];
    use HasFactory;

    protected $casts = [
        'id' => 'string'
    ];

    public function getProject(){
        return $this->belongsTo(Project::class, 'project_id', 'code');
    }
}
