<?php

namespace App\Models;
use App\Models\OffSaleItem;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OffSale extends Model
{
    use HasFactory;
    protected $fillable =[
        "pharmacy_id",
        "customer_name",
        "customer_contact_number",
        "grand_discount_total",
        "grand_total"
    ];
    public function saleItems():HasMany
     {
        return $this->hasMany(OffSaleItem::class,'off_sales_id');
     }
}
