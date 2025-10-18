<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiderShift extends Model
{
    use HasFactory;
    protected $fillable=[
        "rider_id",
        "division",
        "district",
        "upazilla",
        "startDate",
        "endDate",
        "startTime",
        "endTime",
        "status"
    ];
    public function rider():BelongsTo
    {
       return $this->belongsTo(Rider::class,'rider_id');
    }
}
