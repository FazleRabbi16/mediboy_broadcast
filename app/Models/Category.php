<?php
namespace App\Models;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use \Staudenmeir\EloquentEagerLimit\HasEagerLimit;
use App\Models\Category;

class Category extends Model
{
    use HasFactory , HasEagerLimit;
    protected $fillable = [
       'name',
    ];
    // Reffer multiple product under single category

    public function products():HasMany
    {
        return $this->hasMany(Product::class);
    }
}

