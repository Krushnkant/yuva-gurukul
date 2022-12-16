<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventFees extends Model
{
    use HasFactory;
    protected $table = "event_fees";
    protected $fillable = ['event_id','from_age','to_age','fees'];
}
