<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RateTrip extends Model
{
    use HasFactory;
    protected $fillable = [
        'id', 'trip_id', 'customer_id', 'driver_id', 'customer_is_rate', 'driver_is_rate',
        'customer_rate', 'driver_rate', 'updated_at', 'created_at'
    ];
}
