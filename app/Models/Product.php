<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Category;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'image',
        'name',
        'barcode',
        'sku',
        'category_id',
        'price',
        'quantity',
        'user_id',
    ];

    public function getImageAttribute()
    {
        return $this->attributes['image'] ? $this->attributes['image'] : null;
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
