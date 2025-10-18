<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\RiderFile;

use URL;

class Rider extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $appends = ['rider_profile_image_path'];
    protected $fillable=[
        "firstName",
        "lastName",
        "contact",
        "email",
        "password",
        "profileImage",
        "vehicle",
        "currAdd_division",
        "currAdd_district",
        "currAdd_upazilla",
        "perAdd_division",
        "perAdd_district",
        "perAdd_upazilla",
        "eighteenPlus",
        "file",
        "status",
        "ref_id",
        "agreementSigned"
    ];
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];
    // Reffer multiple image under single product
    public function riderDocuments():HasMany
    {
       return $this->hasMany(RiderFile::class);
    }
    // cover image path
    public function getRiderProfileImagePathAttribute()
    {
       return URL::to('/').'/storage/rider_profile_image/'. $this->profileImage;
    }
}
