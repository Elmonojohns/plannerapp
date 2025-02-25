<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstateIndicatorJustify extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function whoUserCiclyOne()
    {
        return $this->belongsTo(User::class, 'responsible_indicator');
    }
}
