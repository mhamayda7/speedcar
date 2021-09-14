<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Point extends Model
{
    // use HasFactory;
    protected $fillable = [
        'id','customer_id', 'trip_id', 'type', 'points', 'details', 'updated_at', 'created_at'
    ];
}
