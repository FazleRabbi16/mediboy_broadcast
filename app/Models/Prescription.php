<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use URL;
class Prescription extends Model
{
    use HasFactory;
    protected $appends = ['prescription_image_path'];
    protected $fillable =[
            'user_id',
            'image'
    ];
    public function getPrescriptionImagePathAttribute()
     {
        return URL::to('/').'/storage/prescription/'. $this->image;
     }
}
