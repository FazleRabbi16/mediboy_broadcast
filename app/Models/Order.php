<?php

namespace App\Models;


use App\Models\Pharmacy;
use App\Models\OrderItem;
use App\Models\DeliveryAddress;
use App\Models\OrderPrescription;
use App\Models\CancelOrderDetails;
use App\Models\DeliveryTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;
    protected $fillable=[
      "user_id",
      "pharmacy_id",
      "orderNo",
      "type",
      "area",
      "offer_total_amount",
      "deliveryCharge",
      "offer_grandTotal",
      "offer_deliveryCharge",
      "comission_amount",
      "payment_method",
      "status",
      "orderDate",
      "delivery_confirmation_code",
      "coupon"
    ];
    // Reffer multiple image under single product
    public function orderItems():HasMany
    {
       return $this->hasMany(OrderItem::class);
    }
    public function deliveryToAddress():HasOne
    {
      return $this->hasOne(DeliveryTo::class,'order_id');
    }
    // cancel data pick
    public function cancelData():HasOne
    {
      return $this->hasOne(CancelOrderDetails::class);
    }
    public function orderPrescriptions():HasMany
    {
       return $this->hasMany(OrderPrescription::class);
    }
    public function pharmacy():BelongsTo
    {
       return $this->belongsTo(Pharmacy::class,'pharmacy_id');
    }
    public function deliveryAddress():BelongsTo
    {
       return $this->belongsTo(DeliveryAddress::class,'delivery_address_id');
    }
    public function users():BelongsTo
    {
       return $this->belongsTo(User::class,'user_id');
    }
}
