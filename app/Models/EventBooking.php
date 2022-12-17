<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventBooking extends Model
{
    use HasFactory;
    protected $table = "event_booking";

    public function event(){
        return $this->hasOne(Event::class,'id','event_id');
    }
}
