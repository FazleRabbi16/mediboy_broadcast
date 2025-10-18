<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Rider;


class RiderWallet extends Model
{
    use HasFactory;
    protected $fillable=[
        "rider_id",
        "balance"
    ];
    //product belongs to a perticular category
    public function rider():BelongsTo
    {
       return $this->belongsTo(Rider::class,'rider_id');
    }
}
