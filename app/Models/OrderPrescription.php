<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use URL;

class OrderPrescription extends Model
{
    use HasFactory;
    protected $appends = ['prescription_copy_path'];
    protected $fillable=[
        'order_id',
        'prescription_copy'
    ];
    public function precription():BelongsTo
    {
       return $this->belongsTo(Prescription::class,'prescription_id');
    }
    public function getPrescriptionCopyPathAttribute()
     {
        return URL::to('/').'/storage/order_prescription/'. $this->prescription_copy;
     }
}
