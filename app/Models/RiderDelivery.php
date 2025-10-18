<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Order;
use App\Models\Rider;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiderDelivery extends Model
{
    use HasFactory;
    protected $fillable=[
        "rider_id",
        "order_id",
        "pharmacy_id",
        "user_id",
        "active_date_time",
        "pickup_date_time",
        "delivered_date_time",
        "cancel_date_time",
        "earn",
        "cod",
        "status"
    ];
    public function orderDetails():BelongsTo
    {
      return $this->BelongsTo(Order::class,'order_id');
    }
    public function rider():BelongsTo
    {
      return $this->BelongsTo(Rider::class,'rider_id');
    }
}
