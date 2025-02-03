<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'price', 'user_id', 'is_paid', 'authorization_code'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
