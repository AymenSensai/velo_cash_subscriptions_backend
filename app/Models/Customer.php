<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;


    protected $fillable = [
        'name',
        'phone_number',
        'email',
        'address',
        'vat_number',
        'user_id',
        'company_name',
        'mobile_number',
        'responsible_name',
        'has_subscription',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
