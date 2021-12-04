<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class placefav extends Model
{
    use HasFactory;
    protected $fillable = [
        'id','customer_id', 'address', 'lat', 'lng', 'updated_at', 'created_at'
    ];
}
