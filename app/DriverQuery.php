<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DriverQuery extends Model
{
    protected $fillable = [
        'id', 'full_name','phone_number','email', 'description','status'
    ];
}
