<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EventBooking;
use App\Models\EventBookingPerson;
use Illuminate\Support\Facades\Validator;

class EvenBookingController extends BaseController
{
    public function eventBooking(Request $request){
        $messages = [
            'user_id.required' =>'Please provide a User Id',
            'event_id.required' =>'Please provide a Event Id',
            'amount.required' =>'Please provide a Amount',
            'total_person.required' =>'Please provide a Total Person'
        ];

        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'event_id' => 'required',
            'amount' => 'required',
            'total_person' => 'required'
        ], $messages);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), "Validation Errors", []);
        }
        
        $event_boking = new EventBooking();
        $event_boking->user_id = $request->user_id;
        $event_boking->event_id = $request->event_id;
        $event_boking->amount = $request->amount;
        $event_boking->total_person = $request->total_person;
        $event_boking->created_at = new \DateTime(null, new \DateTimeZone('Asia/Kolkata'));
        $event_boking->save();

        if($event_boking){
            if(isset($request->family_member_id) && $request->family_member_id != ""){
              $family_ids  = explode(",",$request->family_member_id);
              foreach($family_ids as $family_id){
                    $family_boking = new EventBookingPerson();
                    $family_boking->user_id = $family_id;
                    $family_boking->event_booking_id = $event_boking->id;
                    $family_boking->created_at = new \DateTime(null, new \DateTimeZone('Asia/Kolkata'));
                    $family_boking->save();
              }
            }
        }
        return $this->sendResponseSuccess("Event Booking Successfully");
    }
}
