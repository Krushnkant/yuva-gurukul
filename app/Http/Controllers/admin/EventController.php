<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventFees;
use App\Models\User;
use App\Models\Notification;
use App\Models\EventBooking;
use App\Models\EventHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class EventController extends Controller
{
    public function index(){

        $usersArr = User::where('estatus',1)->whereIn('role',[2,3])->get()->toArray();
        $zonesArr = array(); //Zone::where('estatus',1)->get()->toArray();
        
        $page = 'Event';
        return view('admin.events.list',compact('usersArr', 'zonesArr', 'page'));
    }

    public function addorupdateevent(Request $request){
        
        $messages = [
            'event_image.image' => 'Please provide a Valid Extension Image(e.g: .jpg .png)',
            'event_image.mimes' => 'Please provide a Valid Extension Image(e.g: .jpg .png)',
            'event_title.required' => 'Please provide a Event Title',
            'event_description.required' => 'Please provide a Description',
            'eventStartTime.required' => 'Please provide a Event Start Time.',
            'eventEndTime.required' => 'Please provide a Event End Time.',
        ];

        if (isset($request->action) && $request->action=="update"){
            $validator = Validator::make($request->all(), [
                'event_title' => 'required',
                'event_description' => 'required',
                'eventStartTime' => 'required',
                'eventEndTime' => 'required',
              
            ], $messages);
        }
        else{
            $validator = Validator::make($request->all(), [
                'event_image' => 'required|image|mimes:jpeg,png,jpg',
                'event_title' => 'required',
                'event_description' => 'required',
                'eventStartTime' => 'required',
                'eventEndTime' => 'required',
              
            ], $messages);
        }

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors(),'status'=>'failed']);
        }

        if(isset($request->action) && $request->action == "update"){
            //$event_fees = EventFees::where('event_id',$request->eventId)->delete();
           
            $action = "update";
            $event = Event::find($request->eventId);

            if(!$event){
                return response()->json(['status' => '400']);
            }

            $old_image = $event->event_image;
            $image_name = $old_image;
        }
        else{
            $action = "add";
            $event = new Event();
            $event->estatus = 1;
            $event->created_at = new \DateTime(null, new \DateTimeZone('Asia/Kolkata'));
            $image_name=null;
        }

        if ($request->hasFile('event_image')) {
            $image = $request->file('event_image');
            $image_name = 'eveBanner_' . rand(111111, 999999) . time() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('images/event_image');
            $image->move($destinationPath, $image_name);
            if(isset($old_image)) {
                $old_image = public_path('images/event_image/' . $old_image);
                if (file_exists($old_image)) {
                    unlink($old_image);
                }
            }
            $event->event_image = $image_name;
        }

        $event->event_title = $request->event_title;
        $event->event_description = $request->event_description;
        $event->event_start_time = $request->eventStartTime;
        $event->event_end_time = $request->eventEndTime;
        $event->event_type = $request->gender;

        if($event->save()){
            $eventFees = EventFees::where('form_id',$request->form_id)->get();
            foreach($eventFees as $eventFee){
                $Fees = EventFees::find($eventFee->id);
                $Fees->event_id = $event->id;
                $Fees->save();
            }
        }

        //send notification to customers
        if ($action == "add"){
            $this->sendnotificationevent($event->id);
        }
    
        return response()->json(['status' => '200', 'action' => $action]);
    }

    public function addorupdateeventfree(Request $request){
        
        $messages = [
            'fromAge.required' => 'Please provide age.',
            'toAge.required' => 'Please provide age.',
            'eventFee.required' => 'Please provide a Event Fee.',
        ];
        $validator = Validator::make($request->all(), [
            'fromAge' => 'required',
            'toAge' => 'required',
            'eventFee' => 'required'
            
        ], $messages);
        

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors(),'status'=>'failed']);
        }
        
        $fromAge = (int)$request->fromAge;
        $toAge = (int)$request->toAge;
        $AgeRangeCheckfromAge = EventFees::whereRaw("? BETWEEN from_age AND to_age", [$fromAge])->where('form_id',$request->form_id)->first();
        $AgeRangeChecktoAge = EventFees::whereRaw("? BETWEEN from_age AND to_age", [$toAge])->where('form_id',$request->form_id)->first();
        if($AgeRangeCheckfromAge == null && $AgeRangeCheckfromAge == "" && $AgeRangeChecktoAge == null && $AgeRangeChecktoAge == ""){

            $eventFees = new EventFees();
            $eventFees->form_id = $request->form_id;
            $eventFees->from_age = $request->fromAge;
            $eventFees->to_age = $request->toAge;
            $eventFees->fees = $request->eventFee;
            $eventFees->created_at = new \DateTime(null, new \DateTimeZone('Asia/Kolkata'));
            $eventFees->save();

        }else{
            return response()->json(['status' => '400' ,'message' => 'This age range allready added']);  
        }
        
        return response()->json(['status' => '200', 'data' => $eventFees]);
    }

    public function addorupdatescanneruser(Request $request){
       
        $messages = [
            'event_id.required' => 'Please provide a Event Id.',
        ];
        $validator = Validator::make($request->all(), [
            'event_id' => 'required'
        ], $messages);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors(),'status'=>'failed']);
        }

        EventHandler::where('event_id',$request->event_id)->delete();

        foreach($request->scanner_user as $scanner_user){
            $EventHandler = New EventHandler();
            $EventHandler->event_id = $request->event_id;
            $EventHandler->user_id = $scanner_user;
            $EventHandler->save();
        }
    
        return response()->json(['status' => '200']);
    }

    public function allEventlists(Request $request){
        if ($request->ajax()) {

            $columns = array(
                0 => 'id',
                1 => 'banner',
                2 => 'title',
                3 => 'fees',
                4 => 'startDate',
                5 => 'endDate',
                6 => 'created_at',
                7 => 'action',
            );

            $estatus = 1;
            $totalData = Event::where('estatus',$estatus);
            if (isset($estatus)){
                $totalData = $totalData->where('estatus',$estatus);
            }
            $totalData = $totalData->count();

            $totalFiltered = $totalData;

            $limit = $request->input('length');
            $start = $request->input('start');
            // dd($columns[$request->input('order.0.column')]);
            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir');
            
            if($order == "id"){
                $order = "created_at";
                $dir = 'desc';
            }

            if($order == "title"){
                $order = "event_title";
            }

            if(empty($request->input('search.value')))
            {
                $events = Event::where('estatus',$estatus);
                $events = $events->offset($start)
                    ->limit($limit)
                    ->orderBy($order,$dir)
                    ->get();
            }
            else {
                $search = $request->input('search.value');
                $events = Event::where('estatus',$estatus);
                
                $events = $events->where(function($query) use($search){
                      $query->where('id','LIKE',"%{$search}%")
                            ->orWhere('event_title', 'LIKE',"%{$search}%")
                            ->orWhere('event_start_time', 'LIKE',"%{$search}%")
                            ->orWhere('event_end_time', 'LIKE',"%{$search}%")
                            ->orWhere('created_at', 'LIKE',"%{$search}%");
                      })
                      ->offset($start)
                      ->limit($limit)
                      ->orderBy($order,$dir)
                      ->get();

                $totalFiltered = Event::where('estatus',$estatus);
                if (isset($estatus)){
                    $totalFiltered = $totalFiltered->where('estatus',$estatus);
                }
                $totalFiltered = $totalFiltered->where(function($query) use($search){
                        $query->where('id','LIKE',"%{$search}%")
                            ->orWhere('event_title', 'LIKE',"%{$search}%")
                            ->orWhere('event_start_time', 'LIKE',"%{$search}%")
                            ->orWhere('event_end_time', 'LIKE',"%{$search}%")
                            ->orWhere('created_at', 'LIKE',"%{$search}%");
                        })
                        ->count();
            }

            $data = array();

            if(!empty($events))
            {
                // $i=1;
                foreach ($events as $event)
                {
                    $FeeStr = '';
                    $eventFeesData = EventFees::where('event_id', $event->id)->get();
                    if(!empty($eventFeesData)){
                        foreach ($eventFeesData as $eventFee){

                            $FeeStr .= '<span><i class="fa fa-inr" aria-hidden="true"></i> '.$eventFee->fees. " for Age ".$eventFee->from_age." to ".$eventFee->to_age.'</span>'; 
                        }
                    }

                    if(isset($event->event_image) && $event->event_image != null){
                        $event_image = url('images/event_image/'.$event->event_image);
                    }
                    else{
                        $event_image = url('images/avatar.jpg');
                    }

                    $event_title = "";
                    if(isset($event->event_title)){
                        $event_title = $event->event_title;
                    }

                    $eventStartDate = '<i class="fa fa-calendar" aria-hidden="true"></i> '.date('d-m-Y h:i A', strtotime($event->event_start_time));
                    $eventEndDate = '<i class="fa fa-calendar" aria-hidden="true"></i> '.date('d-m-Y h:i A', strtotime($event->event_end_time));

                    $action = '<button id="addScannerUser" class="btn btn-gray text-primary btn-sm" data-toggle="modal" data-target="#ScannerUserModal" onclick="" data-id="' .$event->id. '"><i class="fa fa-qrcode" aria-hidden="true"></i></button>';
                    $action .= '<button id="editEventBtn" class="btn btn-gray text-blue btn-sm" data-toggle="modal" data-target="#eventModal" onclick="" data-id="' .$event->id. '"><i class="fa fa-pencil" aria-hidden="true"></i></button>';
                    $action .= '<button id="deleteEventBtn" class="btn btn-gray text-danger btn-sm" data-toggle="modal" data-target="#DeleteEventModal" onclick="" data-id="' .$event->id. '"><i class="fa fa-trash-o" aria-hidden="true"></i></button>';
                    $action .= '<button id="bookingUser" class="btn btn-gray text-success btn-sm" onclick="" data-id="' .$event->id. '"><i class="fa fa-ticket" aria-hidden="true"></i></button>';
                    $action .= '<button id="resendEventNotificationBtn" class="btn btn-gray text-blue btn-sm" data-id="'.$event->id.'">Send Event Notification</button>';
                    $action .= '<button id="resendBookingNotificationBtn" class="btn btn-gray text-blue btn-sm" data-id="'.$event->id.'">Send Booking Notification</button>';
                    // $nestedData['id'] = $i;
                    $nestedData['banner'] = '<img src="'. $event_image .'" width="40px" height="40px" alt="Event Banner">';
                    $nestedData['title'] = $event_title;
                    $nestedData['fees'] = $FeeStr;
                    $nestedData['startDate'] = $eventStartDate;
                    $nestedData['endDate'] = $eventEndDate;
                    $nestedData['created_at'] = date('d-m-Y h:i A', strtotime($event->created_at));
                    $nestedData['action'] = $action;
                    $data[] = $nestedData;
                    // $i=$i+1;
                }
            }

            $json_data = array(
                "draw"            => intval($request->input('draw')),
                "recordsTotal"    => intval($totalData),
                "recordsFiltered" => intval($totalFiltered),
                "data" => $data,
            );

            // return json_encode($json_data);
            echo json_encode($json_data);
        }
    }

    public function editevent($id){
        $event = Event::with('event_fees')->find($id);
        return response()->json($event);
    }

    public function deleteevent($id){
        $event = Event::find($id);
        if ($event){
            $image = $event->event_image;
            $event->delete();

            $image = public_path('images/event_image/' . $image);
            if (file_exists($image)) {
                unlink($image);
            }
            return response()->json(['status' => '200']);
        }
        return response()->json(['status' => '400']);
    }

    public function deleteeventfree($id){
        $eventfree = EventFees::find($id);
        if ($eventfree){
            $eventfree->delete();
            return response()->json(['status' => '200']);
        }
        return response()->json(['status' => '400']);
    }

    public function editscanneruser($id){

        $user = EventHandler::where('event_id',$id)->get()->pluck('user_id');
        return response()->json($user);
    }

    public function sendnotificationevent($id){
        $event = Event::find($id);
        if ($event){
            $notification_array['title'] = $event->event_title;
            $notification_array['message'] = $event->event_description;
            // $notification_array['notificationdata'] = $notification_arr;
            $notification_array['image'] = url('images/event_image/' .$event->event_image);
            $notificationsend = sendPushNotification_event($notification_array);
            if($notificationsend){
                $userids = \App\Models\CustomerDeviceToken::pluck('user_id')->all();
                foreach($userids as $userid){
                    $notification = New Notification();
                    $notification->user_id = $userid;
                    $notification->notify_title = $event->event_title;
                    $notification->notify_desc = $event->event_description;
                    $notification->notify_thumb = url('images/event_image/' .$event->event_image);
                    $notification->value_id = $event->id;
                    $notification->type = 'event';
                    $notification->save();
                }

                return response()->json(['status' => '200']);
            }else{
                return response()->json(['status' => '400']);
            } 
        }
        return response()->json(['status' => '400']);
    }


    public function sendnotificationbooking($id){
        $event = Event::find($id);
        if ($event){
            $notification_array['title'] = "Event Remainder";
            $notification_array['message'] = $event->event_title;
            // $notification_array['notificationdata'] = $notification_arr;
            $notification_array['image'] = url('images/event_image/' .$event->event_image);
            $bookinguserids = EventBooking::where('event_id',$id)->get()->pluck('user_id');
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
        return response()->json(['status' => '400']);
    }

    
}
