<?php

namespace App\Models;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderItem extends Model
{
    use HasFactory;
    protected $fillable=[
        "order_id",
        "product_id",
        "discount_unit_price",
        "offer_unit_price",
        "quantity",
        "total_price"
    ];
    public function order():BelongsTo
    {
       return $this->belongsTo(Order::class);
    }
    public function product():BelongsTo
    {
       return $this->belongsTo(Product::class,'product_id');
    }
    public function lastStockProduct()
    {
        return $this->hasOne(StockProduct::class, 'product_id','product_id')
            ->orderByDesc('id')
            ->take(1);
    }
}
