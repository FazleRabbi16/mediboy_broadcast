<?php

namespace App\Models;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CurrentStock extends Model
{
    use HasFactory;
    public $timestamps = true;
    protected $fillable=[
     'pharmacy_id',
     'product_id',
     'in_stock',
     'sale_price',
     'discount_price',
     'peak_hour_price',
     'mediboy_offer_price'
    ];
public function product():BelongsTo
    {
       return $this->belongsTo(Product::class);
    }
public function pharmacy():BelongsTo
    {
       return $this->belongsTo(Pharmacy::class);
    }
}
