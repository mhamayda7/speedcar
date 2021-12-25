<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckPhone extends Model
{
    use HasFactory;

    protected $table = 'check_phone';
    protected $fillable = [
        'id', 'fcm_token', 'phone_number', 'created_at', 'updated-at',
    ];
}
