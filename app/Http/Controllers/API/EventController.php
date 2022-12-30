<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\EventFees;
use App\Models\EventBooking;
use App\Models\User;
use App\Models\Notification;
use App\Models\Banner;
use App\Models\EventHandler;
use Illuminate\Support\Facades\Validator;
use App\Http\Helpers;
use Carbon\Carbon;

class EventController extends BaseController
{
    public function getHome()
    {
        $btemp = array();
        $banner_array = array();
        $banners = Banner::where('estatus',1)->get();
        foreach($banners as $banner){
            $btemp['id'] = $banner->id;
            $btemp['event_image'] = ($banner->banner_thumb != "")?url($banner->banner_thumb):"";
            $btemp['url'] = ($banner->url != "")?$banner->url:"";
            array_push($banner_array,$btemp);
        }

        $event = Event::where( Event::raw('now()'), '<=', Event::raw('event_start_time'))->orderBy('event_start_time', 'asc')->first();
        $events_arr = array();
        $temp = array();
        $family_array = array();
        $temp['id'] = $event->id;
        $temp['title'] = $event->event_title;
        $temp['event_image'] = ($event->event_image != "")?url('/images/event_image/'.$event->event_image):"";
        $temp['event_description'] = $event->event_description;
        $temp['event_start_time'] = date('d-m-Y h:i A', strtotime($event->event_start_time));;
        $temp['event_end_time'] = date('d-m-Y h:i A', strtotime($event->event_end_time));
        $temp['event_type'] = $event->event_type;
        $temp['event_fees'] = $event->event_fees;
        $temp['family_member'] = $family_array;
        array_push($events_arr,$temp);

        
        $data['banners'] = $banner_array;
        $data['upcoming_event'] = $events_arr;
        return $this->sendResponseWithData($data,"Home Retrieved Successfully.");
    }

    public function getEvents($id){
       
        $events = Event::with('event_fees')->where('estatus',1)->get();

        
        $events_arr = array();
        $family_array = array();
        foreach ($events as $event){
            $scanner = 0;
            $event_handler = EventHandler::where('event_id',$event->id)->where('user_id',$id)->get();
            if($event_handler){
              $scanner = 1;  
            }
            $temp = array();
            $temp['id'] = $event->id;
            $temp['title'] = $event->event_title;
            $temp['event_image'] = ($event->event_image != "")?url('/images/event_image/'.$event->event_image):"";
            $temp['event_description'] = $event->event_description;
            $temp['event_start_time'] = date('d-m-Y h:i A', strtotime($event->event_start_time));;
            $temp['event_end_time'] = date('d-m-Y h:i A', strtotime($event->event_end_time));
            $temp['event_type'] = $event->event_type;
            $temp['event_fees'] = $event->event_fees;
            $temp['family_member'] = $family_array;
            $temp['scanner'] = $scanner;
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

       
        
        $event = Event::with('event_fees')->where('id',$request->event_id)->where('estatus',1)->first();
        if (!$event){
            return $this->sendError("Event Not Exist", "Not Found Error", []);
        }
        $family_member_array = array();
        $family_array = array();

        if($user){
            $age = (int)$this->age($user->birth_date);
            $family_member_array['id'] = $user->id;
            $family_member_array['first_name'] = $user->first_name;
            $family_member_array['middle_name'] = $user->middle_name;
            $family_member_array['last_name'] = $user->last_name;
            $family_member_array['age'] = $age;
            
            $AgeRangeCheck = EventFees::whereRaw("? BETWEEN from_age AND to_age", [$age])->where('event_id',$event->id)->first();
            $family_member_array['fees'] = isset($AgeRangeCheck->fees)?$AgeRangeCheck->fees:0;
            array_push($family_array,$family_member_array);
        }

        foreach($user->family_member as $family_member){
          $age = (int)$this->age($family_member->birth_date);
          $family_member_array['id'] = $family_member->id;
          $family_member_array['first_name'] = $family_member->first_name;
          $family_member_array['middle_name'] = $family_member->middle_name;
          $family_member_array['last_name'] = $family_member->last_name;
          $family_member_array['age'] = $age;
          
          $AgeRangeCheck = EventFees::whereRaw("? BETWEEN from_age AND to_age", [$age])->where('event_id',$event->id)->first();
          $family_member_array['fees'] = isset($AgeRangeCheck->fees)?$AgeRangeCheck->fees:0;
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
        $temp['event_fees'] = $event->event_fees;
        $temp['family_member'] = $family_array;
        array_push($events_arr,$temp);
        

        return $this->sendResponseWithData($events_arr,"Event Retrieved Successfully.");
    }

    public function age($birth_date)
    {
        return Carbon::parse($birth_date)->age;
    }

    public function viewSummary(Request $request){

        $validator = Validator::make($request->all(), [
            'event_id' => 'required',
            'user_id' => 'required',
        ]);

        if($validator->fails()){
            return $this->sendError($validator->errors(), "Validation Errors", []);
        }

        $user = User::where('id',$request->user_id)->where('estatus',1)->first();
        if (!$user){
            return $this->sendError("User Not Exist", "Not Found Error", []);
        }

    
        $event = Event::where('id',$request->event_id)->where('estatus',1)->first();
        if (!$event){
            return $this->sendError("Event Not Exist", "Not Found Error", []);
        }

        $users = User::where('parent_id',$request->user_id)->where('estatus',1)->get();
        $book_data_array = array();
        $data_array = array();
        $book_child_array = array();
        $child_array = array();

        foreach($users as $cli_user){
          $event_booking = EventBooking::where('user_id',$cli_user->id)->where('estatus',1)->first();
          if($cli_user->role == 2){
            $role = "Karykarta";
          }else{
            $role = "Haribhagat";
          } 
          if($event_booking){
            $book_child_array['id'] = $cli_user->id;
            $book_child_array['first_name'] = $cli_user->first_name;
            $book_child_array['middle_name'] = $cli_user->middle_name;
            $book_child_array['last_name'] = $cli_user->last_name;
            $book_child_array['mobile_no'] = $cli_user->mobile_no;
            $book_child_array['profile_pic'] = isset($cli_user->profile_pic) ? $cli_user->profile_pic : asset('images/default_avatar.jpg');
            $book_child_array['role'] = $role;
            array_push($book_data_array,$book_child_array);
          }else{
            $child_array['id'] = $cli_user->id;
            $child_array['first_name'] = $cli_user->first_name;
            $child_array['middle_name'] = $cli_user->middle_name;
            $child_array['last_name'] = $cli_user->last_name;
            $child_array['mobile_no'] = $cli_user->mobile_no;
            $child_array['profile_pic'] = isset($cli_user->profile_pic) ? $cli_user->profile_pic : asset('images/default_avatar.jpg');
            $book_child_array['role'] = $role;
            array_push($data_array,$child_array);
          }
        }

        $data['participate_member'] = $book_data_array;
        $data['not_participate_member'] = $data_array;
        return $this->sendResponseWithData($data,"Summary Retrieved Successfully.");
    }


    public function sendnotificationbookingremainder(){
        $events = Event::whereDate('event_start_time', Carbon::today())->get();
        foreach($events as $event){
            if($event){
                $notification_array['title'] = "Event Remainder";
                $notification_array['message'] = $event->event_title;
                // $notification_array['notificationdata'] = $notification_arr;
                $notification_array['image'] = url('images/event_image/' .$event->event_image);
                $bookinguserids = EventBooking::where('event_id',$event->id)->get()->pluck('user_id');
                if(count($bookinguserids)){
                    $notificationsend = sendPushNotification_remainder($bookinguserids,$notification_array);
                    if($notificationsend){
                        $userids = \App\Models\CustomerDeviceToken::pluck('user_id')->all();
                        foreach($userids as $userid){
                            $notification = New Notification();
                            $notification->user_id = $userid;
                            $notification->notify_title = "Event Remainder";
                            $notification->notify_desc = $event->event_title;
                            $notification->notify_thumb = url('images/event_image/' .$event->event_image);
                            $notification->value_id = $event->id;
                            $notification->type = 'remainder';
                            $notification->save();
                        }
                        return response()->json(['status' => '200']);
                    }else{
                        return response()->json(['status' => '400']);
                    }
                }else{
                    return response()->json(['status' => '200']);
                } 
            }
        }
        return response()->json(['status' => '400']);
    }
}
