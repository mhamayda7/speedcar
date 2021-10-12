<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NotificationMessage extends Model
{
     protected $fillable = [
        'id','user_id','country_id','type','title','message','image','status','created_at','updated_at'
    ];
}
