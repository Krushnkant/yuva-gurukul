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

    public function allbookinglist(Request $request){
        if ($request->ajax()) {

            $columns = array(
                0 => 'id',
                1 => 'name',
                2 => 'title',
                3 => 'total_person',
                4 => 'total_amount',
                5 => 'payment_transaction_id',
                6 => 'created_at',
                7 => 'action',
            );

            $estatus = 1;
            $totalData = EventBooking::where('estatus',$estatus);
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
                $events = EventBooking::where('estatus',$estatus);
                $events = $events->offset($start)
                    ->limit($limit)
                    ->orderBy($order,$dir)
                    ->get();
            }
            else {
                $search = $request->input('search.value');
                $events = EventBooking::where('estatus',$estatus);
                
                $events = $events->where(function($query) use($search){
                      $query->where('id','LIKE',"%{$search}%")
                           
                      })
                      ->offset($start)
                      ->limit($limit)
                      ->orderBy($order,$dir)
                      ->get();

                $totalFiltered = EventBooking::where('estatus',$estatus);
                if (isset($estatus)){
                    $totalFiltered = $totalFiltered->where('estatus',$estatus);
                }
                $totalFiltered = $totalFiltered->where(function($query) use($search){
                        $query->where('id','LIKE',"%{$search}%")
                           
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
}
