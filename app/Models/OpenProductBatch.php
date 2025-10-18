<?php

namespace App\Models;

use App\Models\Stock;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpenProductBatch extends Model
{
    use HasFactory;
    protected $fillable=[
        "pharmacy_id",
        "product_stock_id",
        "product_id",
        "batch_no",
        "status",
        "purchase_price",
        "offer_price",
        "available",
        "qty_stock",
        "mfg_date",
        "exp_date"
    ];
    public function stock():BelongsTo
    {
        return $this->belongsTo(Stock::class, 'batch_no', 'batch_no');
    }
}
