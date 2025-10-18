<?php

namespace App\Models;

use App\Models\Stock;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StockProduct extends Model
{
    use HasFactory;
    protected $fillable=[
        'pharmacy_id',
        'product_id',
        'stock_mrp',
        'purchase_price',
        'batch_no',
        'offer_price',
        'perc_off',
        'mfg_date',
        'expire_date',
        'qty',
        'shelf'
    ];
    public function product()
    {
        return $this->belongsTo(Product::class,'product_id');
    }
  //product in stock
  public function stock():HasOne
  {
     return $this->hasOne(Stock::class);
  }
}
