<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\DeliveryAddress;


class ActiveDeliveryAddress extends Model
{
    use HasFactory;
    protected $fillable=[
            "user_id",
            "delivery_address_id"
    ];
     //active address belongs to a perticular addres
     public function address(): BelongsTo
    {
        return $this->belongsTo(DeliveryAddress::class, 'delivery_address_id');
    }
}
