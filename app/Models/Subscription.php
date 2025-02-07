<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'price'];

    public function customers()
    {
        return $this->belongsToMany(Customer::class, 'customer_subscription')
                    ->withPivot('is_paused')
                    ->withTimestamps();
    }
}
