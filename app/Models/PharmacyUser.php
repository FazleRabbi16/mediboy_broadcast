<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;

class PharmacyUser extends Model
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $fillable = [
        'firstName','lastName','phoneNumber','email','password','role','pharmacy_id'
     ];
     protected $hidden = [
        'password',
        'remember_token',
    ];
     public function pharmacy():BelongsTo
     {
         return $this->belongsTo(Pharmacy::class,'pharmacy_id');
     }
}
