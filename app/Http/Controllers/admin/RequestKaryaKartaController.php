<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RequestKaryaKarta;
use App\Models\User;

class RequestKaryaKartaController extends Controller
{
    private $page = "Request KaryaKarta";

    public function index(){
        $action = "list";
        return view('admin.requestkaryakarta.list',compact('action'))->with('page',$this->page);
    }

 

    public function allRequestKaryaKartalist(Request $request){
        if ($request->ajax()) {
            $columns = array(
                0 =>'sr_no',
                1 =>'image',
                2 => 'product',
                3 => 'user',
                4 => 'review_image',
                5 => 'review_text',
                6 => 'review_rating',
              //  7 => 'estatus',
                7 => 'created_at',
                8 => 'action',
            );
            $totalData = RequestKaryaKarta::count();
            $totalFiltered = $totalData;

            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir');

            if($order == "sr_no"){
                $order = "created_at";
                $dir = 'desc';
            }

            if(empty($request->input('search.value')))
            {
                $reviews = RequestKaryaKarta::offset($start)
                    ->limit($limit)
                    ->orderBy($order,$dir)
                    ->get();
              
            }
            else {
                $search = $request->input('search.value');
                $reviews =  RequestKaryaKarta::offset($start)
                    ->limit($limit)
                    ->orderBy($order,$dir)
                    ->get();
                $totalFiltered = RequestKaryaKarta::count();
            }
            //dd($reviews);
            $data = array();

            if(!empty($reviews))
            {
                foreach ($reviews as $review)
                {
                   
                    $userdata = User::where('id',$review->user_id)->first();
                    $full_name = "";
                    if(isset($userdata->first_name)){
                        $full_name = $userdata->first_name;
                    }
                    if(isset($userdata->middle_name) && !empty($userdata->middle_name)){
                        $full_name .= ' '.$userdata->middle_name;
                    }
                    if(isset($userdata->last_name) && !empty($userdata->last_name)){
                        $full_name .= ' '.$userdata->last_name;
                    }

                    $userdata1 = User::where('id',$review->request_by_user_id)->first();
                    $full_name1 = "";
                    if(isset($userdata1->first_name)){
                        $full_name1 = $userdata1->first_name;
                    }
                    if(isset($userdata1->middle_name) && !empty($userdata1->middle_name)){
                        $full_name1 .= ' '.$userdata1->middle_name;
                    }
                    if(isset($userdata1->last_name) && !empty($userdata1->last_name)){
                        $full_name1 .= ' '.$userdata1->last_name;
                    }
                    
                    $action='';
                    if($review->estatus == 1){
                        $action .= '<button id="AcceptBtn" onclick="acceptstatus('. $review->id .')" class="btn btn-success text-white btn-sm" data-id="' .$review->id. '">Accept</button>';
                        $action .= '<button id="Reject" onclick="rejectstatus('. $review->id .')" class="btn btn-danger text-white btn-sm" data-id="' .$review->id. '">Reject</button>';
                    }else if($review->estatus == 2){
                        $action .= 'Accept';
                    }else{
                        $action .= 'Reject';
                    }
                    $nestedData['user'] = $full_name;
                    $nestedData['request_by_user'] = $full_name1;
                    
                    $nestedData['created_at'] = date('d-m-Y h:i A', strtotime($review->created_at));
                    $nestedData['action'] = $action;
                    $data[] = $nestedData;
                }
            }

            $json_data = array(
                "draw"            => intval($request->input('draw')),
                "recordsTotal"    => intval($totalData),
                "recordsFiltered" => intval($totalFiltered),
                "data" => $data,
            );
            echo json_encode($json_data);
        }
    }

    public function rejectstatus($id){
        $requestkaryakarta = RequestKaryaKarta::find($id);
        $requestkaryakarta->estatus = 3;
        $requestkaryakarta->save();
        return response()->json(['status' => '200']);
       
    }

    public function acceptstatus($id){
        $requestkaryakarta = RequestKaryaKarta::find($id);
        $requestkaryakarta->estatus = 2;
        $requestkaryakarta->save();
        if($requestkaryakarta){
            $user = User::where('user_id',$requestkaryakarta->id)->first();
            $user->role = 3;
            $user->save();
        }
        return response()->json(['status' => '200']);
       
    }
}
