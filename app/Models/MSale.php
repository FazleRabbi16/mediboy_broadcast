<?php

namespace App\Models;

use App\Models\User;
use App\Models\MSaleItem;
use App\Models\PharmacyUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class MSale extends Model
{
    use HasFactory;
    protected $fillable =[
      "order_id",
      "user_id",
      "pharmacy_id",
      "pharmacy_seller_name",
      "pharmacy_seller_email",
      "pharmacy_seller_phoneNumber",
      "salesPrice",
      "comission_amount",
      "saleDate",
      "paymentMethod",
      "status",
      "saleItems"
    ];

    public function saleItems():HasMany
     {
        return $this->hasMany(MSaleItem::class,'m_sales_id');
     }

     public function user():BelongsTo
     {
        return $this->belongsTo(User::class,'user_id');
     }
     public function pharmacyUser():BelongsTo
     {
        return $this->belongsTo(PharmacyUser::class,'pharmacy_user_id');
     }

}
