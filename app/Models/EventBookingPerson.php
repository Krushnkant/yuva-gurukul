<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventBookingPerson extends Model
{
    use HasFactory;
    protected $table = "event_event_booking_person";

    public function user(){
        return $this->hasOne(User::class,'id','user_id');
    }
}
