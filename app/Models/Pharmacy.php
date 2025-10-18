<?php

namespace App\Models;

use App\Models\Order;
use App\Models\Pharmacy;
use App\Models\PharmacyBusinessSetup;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use URL;

class Pharmacy extends Model
{
    use HasFactory;
    protected $appends = ['pharmacy_cover_image_path'];
    protected $fillable = [
       'pharmacyName','drug_lic_no','proprietor','contact','division','district','upazilla','area','placeDetails', 'googleLink','openTime','closeTime','offDays','cover_image'
     ];

     public function pharmacy_business():HasOne
     {
        return $this->hasOne(PharmacyBusinessSetup::class);
     }
     public function pharmacy_users():HasMany
     {
        return $this->hasMany(PharmacyUser::class);
     }
     public function orders():HasMany
     {
        return $this->hasMany(Order::class);
     }
     public function stock_products():HasMany
     {
        return $this->hasMany(StockProduct::class,'pharmacy_id', 'id');
     }
     // cover image path
     public function getPharmacyCoverImagePathAttribute()
     {
        return URL::to('/').'/storage/pharmacy_cover_images/'. $this->cover_image;
     }

}
