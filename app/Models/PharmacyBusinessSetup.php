<?php

namespace App\Models;

use App\Models\Pharmacy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PharmacyBusinessSetup extends Model
{
    //pharmacis and PhramacyBusinessSetup one to one relation . One business setup of a pharmacy is one
    use HasFactory;
    protected $fillable = [
        'status','feature','comissionPercent','pharmacy_id'
     ];
    public function pharmacy()
    {
        return $this->belongsTo(Pharmacy::class);
    }
}
