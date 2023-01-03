<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EventBooking;
use App\Models\User;
use App\Models\EventFees;
use App\Models\EventBookingPerson;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class EvenBookingController extends BaseController
{
    public function eventBooking(Request $request){
        $messages = [
            'user_id.required' =>'Please provide a User Id',
            'event_id.required' =>'Please provide a Event Id',
            'amount.required' =>'Please provide a Amount',
            'total_person.required' =>'Please provide a Total Person',
            'payment_transaction_id.required' =>'Please provide a payment transaction id'
        ];

        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'event_id' => 'required',
            'amount' => 'required',
            'total_person' => 'required',
            'payment_transaction_id' => 'required'
        ], $messages);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), "Validation Errors", []);
        }
        
        $event_boking = new EventBooking();
        $event_boking->user_id = $request->user_id;
        $event_boking->event_id = $request->event_id;
        $event_boking->amount = $request->amount;
        $event_boking->total_person = $request->total_person;
        $event_boking->payment_transaction_id = $request->payment_transaction_id;
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
        return $this->sendResponseWithData($event_boking,"Event Booking Successfully");
    }

    public function eventScanner(Request $request){
        $messages = [
            'user_id.required' =>'Please provide a User Id',
            'booking_id.required' =>'Please provide a Booking Id',
        ];

        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'booking_id' => 'required'
        ], $messages);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), "Validation Errors", []);
        }

        $user = User::where('id',$request->user_id)->where('estatus',1)->first();
        if (!$user){
            return $this->sendError("User Not Exist", "Not Found Error", []);
        }

        $booking = EventBooking::find($request->booking_id);
        if (!$booking){
            return $this->sendError("Booking Not Exist", "Not Found Error", []);
        }
    
        $booking->is_present = 1;
        $booking->QR_scan_by = $request->user_id;
        $booking->atten_time = new \DateTime(null, new \DateTimeZone('Asia/Kolkata'));
        $booking->save();

        $family_member_array = array();
        $family_array = array();

        $EventBookingPersons = EventBookingPerson::with('user')->where('event_booking_id',$request->booking_id)->withTrashed()->get();
        foreach($EventBookingPersons as $EventBookingPerson){
          $age = (int)$this->age($EventBookingPerson->user->birth_date);
          $family_member_array['id'] = $EventBookingPerson->user->id;
          $family_member_array['first_name'] = $EventBookingPerson->user->first_name;
          $family_member_array['middle_name'] = $EventBookingPerson->user->middle_name;
          $family_member_array['last_name'] = $EventBookingPerson->user->last_name;
          $family_member_array['age'] = $age;
          
          $AgeRangeCheck = EventFees::whereRaw("? BETWEEN from_age AND to_age", [$age])->where('event_id',$booking->event_id)->first();
          $family_member_array['fees'] = isset($AgeRangeCheck->fees)?$AgeRangeCheck->fees:0;
          array_push($family_array,$family_member_array);
        }

        if($booking){
            $events_arr = array();
            $temp = array();
            $temp['id'] = $booking->id;
            $temp['amount'] = $booking->amount;
            $temp['total_person'] = $booking->total_person;
            $temp['event_title'] = $booking->event->event_title;
            $temp['event_image'] = ($booking->event->event_image != "")?url('/images/event_image/'.$booking->event->event_image):"";
            $temp['event_description'] = $booking->event->event_description;
            $temp['event_start_time'] = date('d-m-Y h:i A', strtotime($booking->event->event_start_time));;
            $temp['event_end_time'] = date('d-m-Y h:i A', strtotime($booking->event->event_end_time));
            $temp['event_type'] = $booking->event->event_type;
            $temp['event_fees'] = $booking->event->event_fees;
            $temp['booking_member'] = $family_array;
            array_push($events_arr,$temp);
        }
        return $this->sendResponseWithData($events_arr,"Event Scanner Successfully");
    }

    public function getBooking(Request $request){
        $messages = [
            'user_id.required' =>'Please provide a User Id',
        ];

        $validator = Validator::make($request->all(), [
            'user_id' => 'required'
        ], $messages);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), "Validation Errors", []);
        }

        $user = User::where('id',$request->user_id)->where('estatus',1)->first();
        if (!$user){
            return $this->sendError("User Not Exist", "Not Found Error", []);
        }

        $bookings = EventBooking::where('user_id',$request->user_id);
        if(isset($request->event_id) && $request->event_id > 0){
            $bookings = $bookings->where('event_id',$request->event_id);
        }
        $bookings = $bookings->orderBy('id','desc')->get();
        $events_arr = array();
        foreach($bookings as $booking){
          
            $family_member_array = array();
            $family_array = array();

            $EventBookingPersons = EventBookingPerson::with('user')->where('event_booking_id',$booking->id)->withTrashed()->get();
            foreach($EventBookingPersons as $EventBookingPerson){
            $age = (int)$this->age($EventBookingPerson->user->birth_date);
            $family_member_array['id'] = $EventBookingPerson->user->id;
            $family_member_array['first_name'] = $EventBookingPerson->user->first_name;
            $family_member_array['middle_name'] = $EventBookingPerson->user->middle_name;
            $family_member_array['last_name'] = $EventBookingPerson->user->last_name;
            $family_member_array['age'] = $age;
            
            $AgeRangeCheck = EventFees::whereRaw("? BETWEEN from_age AND to_age", [$age])->where('event_id',$booking->event_id)->first();
            $family_member_array['fees'] = isset($AgeRangeCheck->fees)?$AgeRangeCheck->fees:0;
            array_push($family_array,$family_member_array);
            }

            if($booking){
               
                $temp = array();
                $temp['id'] = $booking->id;
                $temp['event_id'] = $booking->event->id;
                $temp['amount'] = $booking->amount;
                $temp['total_person'] = $booking->total_person;
                $temp['event_title'] = $booking->event->event_title;
                $temp['event_image'] = ($booking->event->event_image != "")?url('/images/event_image/'.$booking->event->event_image):"";
                $temp['event_description'] = $booking->event->event_description;
                $temp['event_start_time'] = date('d-m-Y h:i A', strtotime($booking->event->event_start_time));;
                $temp['event_end_time'] = date('d-m-Y h:i A', strtotime($booking->event->event_end_time));
                $temp['event_type'] = $booking->event->event_type;
                $temp['event_fees'] = $booking->event->event_fees;
                $temp['booking_member'] = $family_array;
                array_push($events_arr,$temp);
            }
        }
        return $this->sendResponseWithData($events_arr,"Event Booking Successfully");
    }

    public function getBookingDetails(Request $request){
        $messages = [
            'booking_id.required' =>'Please provide a Booking Id',
            // 'event_id.required' =>'Please provide a Event Id',
            // 'user_id.required' =>'Please provide a Event Id',
        ];

        $validator = Validator::make($request->all(), [
            'booking_id' => 'required'
        ], $messages);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), "Validation Errors", []);
        }

        $booking = EventBooking::where('booking_id',$request->booking_id)->first();
        if (!$booking){
            return $this->sendError("Booking Not Exist", "Not Found Error", []);
        }

        
        $events_arr = array();
        $family_member_array = array();
        $family_array = array();

        $EventBookingPersons = EventBookingPerson::with('user')->where('event_booking_id',$booking->id)->withTrashed()->get();
        foreach($EventBookingPersons as $EventBookingPerson){
        $age = (int)$this->age($EventBookingPerson->user->birth_date);
        $family_member_array['id'] = $EventBookingPerson->user->id;
        $family_member_array['first_name'] = $EventBookingPerson->user->first_name;
        $family_member_array['middle_name'] = $EventBookingPerson->user->middle_name;
        $family_member_array['last_name'] = $EventBookingPerson->user->last_name;
        $family_member_array['age'] = $age;
        
        $AgeRangeCheck = EventFees::whereRaw("? BETWEEN from_age AND to_age", [$age])->where('event_id',$booking->event_id)->first();
        $family_member_array['fees'] = isset($AgeRangeCheck->fees)?$AgeRangeCheck->fees:0;
        array_push($family_array,$family_member_array);
        }

        if($booking){
            
            $temp = array();
            $temp['id'] = $booking->id;
            $temp['amount'] = $booking->amount;
            $temp['total_person'] = $booking->total_person;
            $temp['event_title'] = $booking->event->event_title;
            $temp['event_image'] = ($booking->event->event_image != "")?url('/images/event_image/'.$booking->event->event_image):"";
            $temp['event_description'] = $booking->event->event_description;
            $temp['event_start_time'] = date('d-m-Y h:i A', strtotime($booking->event->event_start_time));;
            $temp['event_end_time'] = date('d-m-Y h:i A', strtotime($booking->event->event_end_time));
            $temp['event_type'] = $booking->event->event_type;
            $temp['event_fees'] = $booking->event->event_fees;
            $temp['booking_member'] = $family_array;
            // array_push($events_arr,$temp);
        }
        
        return $this->sendResponseWithData($temp,"Event Booking Details Successfully");
    }

    public function age($birth_date)
    {
        return Carbon::parse($birth_date)->age;
    }

}
