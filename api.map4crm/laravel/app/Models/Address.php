<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    protected $fillable = ['address', 'latitude', 'longitude', 'user_id', 'order_id'];

    public function delivery()
    {
        return $this->belongsTo(Delivery::class, 'order_id');
    }
}
