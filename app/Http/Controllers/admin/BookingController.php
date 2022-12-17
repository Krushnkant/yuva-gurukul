<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EventBooking;
use App\Models\Event;
use App\Models\User;

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
                6 => 'created_at'
            );

            $estatus = 1;
            $totalData = EventBooking::where('event_id',$request->event_id);
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
                $events = EventBooking::where('event_id',$request->event_id);
                $events = $events->offset($start)
                    ->limit($limit)
                    ->orderBy($order,$dir)
                    ->get();
            }
            else {
                $search = $request->input('search.value');
                $events = EventBooking::where('event_id',$request->event_id);
                
                $events = $events->where(function($query) use($search){
                      $query->where('id','LIKE',"%{$search}%");
                      })
                      ->offset($start)
                      ->limit($limit)
                      ->orderBy($order,$dir)
                      ->get();

                $totalFiltered = EventBooking::where('event_id',$request->event_id);
                if (isset($estatus)){
                    $totalFiltered = $totalFiltered->where('estatus',$estatus);
                }
                $totalFiltered = $totalFiltered->where(function($query) use($search){
                        $query->where('id','LIKE',"%{$search}%");
                        })
                        ->count();
            }

            $data = array();

            if(!empty($events))
            {
                foreach ($events as $event)
                {
                    $eventData = Event::where('id', $event->event_id)->first();
                    $user = User::where('id', $event->user_id)->first();
                    $full_name = "";
                    if(isset($user->first_name)){
                        $full_name = $user->first_name;
                    }
                    if(isset($user->middle_name) && !empty($user->middle_name)){
                        $full_name .= ' '.$user->middle_name;
                    }
                    if(isset($user->last_name) && !empty($user->last_name)){
                        $full_name .= ' '.$user->last_name;
                    }
                   
                    $event_title = "";
                    if(isset($eventData->event_title)){
                        $event_title = $eventData->event_title;
                    }
                    $action = '<button id="addScannerUser" class="btn btn-gray text-primary btn-sm" data-toggle="modal" data-target="#ScannerUserModal" onclick="" data-id="' .$event->id. '"><i class="fa fa-qrcode" aria-hidden="true"></i></button>';
                    $action .= '<button id="editEventBtn" class="btn btn-gray text-blue btn-sm" data-toggle="modal" data-target="#eventModal" onclick="" data-id="' .$event->id. '"><i class="fa fa-pencil" aria-hidden="true"></i></button>';
                    $action .= '<button id="deleteEventBtn" class="btn btn-gray text-danger btn-sm" data-toggle="modal" data-target="#DeleteEventModal" onclick="" data-id="' .$event->id. '"><i class="fa fa-trash-o" aria-hidden="true"></i></button>';
                    $action .= '<button id="bookingUser" class="btn btn-gray text-success btn-sm" onclick="" data-id="' .$event->id. '"><i class="fa fa-ticket" aria-hidden="true"></i></button>';
                    // $nestedData['id'] = $i;
                    $nestedData['name'] = $full_name;
                    $nestedData['title'] = $event_title;
                    $nestedData['total_person'] = $event->total_person;
                    $nestedData['total_amount'] = $event->amount;
                    $nestedData['payment_transaction_id'] = $event->payment_transaction_id;
                    $nestedData['created_at'] = date('d-m-Y h:i A', strtotime($event->created_at));
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
