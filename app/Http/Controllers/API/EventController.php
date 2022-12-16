<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\EventFees;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use App\Http\Helpers;
use Carbon\Carbon;

class EventController extends BaseController
{
    public function getHome()
    {
    
        $event = Event::where( Event::raw('now()'), '<=', Event::raw('event_start_time'))->orderBy('event_start_time', 'asc')->first();
        $events_arr = array();
        $temp = array();
        $temp['id'] = $event->id;
        $temp['title'] = $event->event_title;
        $temp['event_image'] = ($event->event_image != "")?url('/images/event_image/'.$event->event_image):"";
        $temp['event_description'] = $event->event_description;
        $temp['event_start_time'] = date('d-m-Y h:i A', strtotime($event->event_start_time));;
        $temp['event_end_time'] = date('d-m-Y h:i A', strtotime($event->event_end_time));
        $temp['event_type'] = $event->event_type;
        $temp['event_fees'] = $event->event_fees;
        array_push($events_arr,$temp);

        $banner = array();
        $data['banners'] = $banner;
        $data['upcoming_event'] = $events_arr;
        return $this->sendResponseWithData($data,"Home Retrieved Successfully.");
    }

    public function getEvents(){
       
        $events = Event::with('event_fees')->where('estatus',1)->get();
        
        $events_arr = array();
        foreach ($events as $event){
            $temp = array();
            $temp['id'] = $event->id;
            $temp['title'] = $event->event_title;
            $temp['event_image'] = ($event->event_image != "")?url('/images/event_image/'.$event->event_image):"";
            $temp['event_description'] = $event->event_description;
            $temp['event_start_time'] = date('d-m-Y h:i A', strtotime($event->event_start_time));;
            $temp['event_end_time'] = date('d-m-Y h:i A', strtotime($event->event_end_time));
            $temp['event_type'] = $event->event_type;
            $temp['event_fees'] = $event->event_fees;
            array_push($events_arr,$temp);
        }

        return $this->sendResponseWithData($events_arr,"Events Retrieved Successfully.");
    }

    public function viewEvent(Request $request){

        $validator = Validator::make($request->all(), [
            'event_id' => 'required',
            'user_id' => 'required',
        ]);

        if($validator->fails()){
            return $this->sendError($validator->errors(), "Validation Errors", []);
        }

        $user = User::with('family_member')->where('id',$request->user_id)->where('estatus',1)->first();
        if (!$user){
            return $this->sendError("User Not Exist", "Not Found Error", []);
        }

       
        
        $event = Event::where('id',$request->event_id)->where('estatus',1)->first();
        if (!$event){
            return $this->sendError("Event Not Exist", "Not Found Error", []);
        }
        $family_member_array = array();
        $family_array = array();
        foreach($user->family_member as $family_member){
          $age = (int)$this->age($family_member->birth_date);
          $family_member_array['id'] = $family_member->id;
          $family_member_array['first_name'] = $family_member->first_name;
          $family_member_array['middle_name'] = $family_member->middle_name;
          $family_member_array['last_name'] = $family_member->last_name;
          $family_member_array['age'] = $age;
          
          $AgeRangeCheck = EventFees::whereRaw("? BETWEEN from_age AND to_age", [$age])->where('event_id',$event->id)->first();
          $family_member_array['fees'] = $AgeRangeCheck->fees;
          array_push($family_array,$family_member_array);
        }
       
        $events_arr = array();
        $temp = array();
        $temp['id'] = $event->id;
        $temp['title'] = $event->event_title;
        $temp['event_image'] = ($event->event_image != "")?url('/images/event_image/'.$event->event_image):"";
        $temp['event_description'] = $event->event_description;
        $temp['event_start_time'] = date('d-m-Y h:i A', strtotime($event->event_start_time));;
        $temp['event_end_time'] = date('d-m-Y h:i A', strtotime($event->event_end_time));
        $temp['event_type'] = $event->event_type;
        $temp['family_member'] = $family_array;
        array_push($events_arr,$temp);
        

        return $this->sendResponseWithData($events_arr,"Event Retrieved Successfully.");
    }

    public function age($birth_date)
    {
        return Carbon::parse($birth_date)->age;
    }

    


}
