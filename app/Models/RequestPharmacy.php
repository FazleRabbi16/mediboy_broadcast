<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestPharmacy extends Model
{
    use HasFactory;
    protected $fillable = [
        'fullName',
        'contact',
        'email',
        'pharmacyName',
        'division',
        'district',
        'upazilla',
        'place',
    ];
}
