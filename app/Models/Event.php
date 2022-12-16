<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;
    protected $table = "event";
    protected $fillable = ['event_title','event_image','event_description','event_time','event_type','estatus'];

    public function event_fees(){
        return $this->hasMany(EventFees::class,'event_id','id');
    }
}
