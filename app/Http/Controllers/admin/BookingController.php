<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EventBooking;

class BookingController extends Controller
{
    private $page = "Booking";

    public function index($id){
        $action = "list";
        return view('admin.bookings.list',compact('action','id'))->with('page',$this->page);
    }
}
