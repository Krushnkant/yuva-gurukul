<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Zone;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index(){

        $usersArr = User::where('estatus',1)->where('role',2)->get()->toArray();
        $zonesArr = Zone::where('estatus',1)->get()->toArray();
        
        $page = 'Users';
        return view('admin.users.list',compact('usersArr', 'zonesArr', 'page'));
    }

    public function addorupdateuser(Request $request){
        $messages = [
            'profile_pic.image' => 'Please provide a Valid Extension Image(e.g: .jpg .png)',
            'profile_pic.mimes' => 'Please provide a Valid Extension Image(e.g: .jpg .png)',
            'first_name.required' => 'Please provide a First Name',
            'middle_name.required' => 'Please provide a Middle Name',
            'last_name.required' => 'Please provide a Last Name',
            'mobile_no.required' => 'Please provide a Mobile No.',
            'email.required' => 'Please provide a Email Address.',
            'dob.required' => 'Please provide a Date of Birth.',
        ];

        if (isset($request->action) && $request->action=="update"){
            $validator = Validator::make($request->all(), [
                'profile_pic' => 'image|mimes:jpeg,png,jpg',
                'first_name' => 'required',
                'middle_name' => 'required',
                'last_name' => 'required',
                'mobile_no' => 'required|numeric|digits:10',
                'email' => 'required|email',
                'dob' => 'required'
            ], $messages);
        }
        else{
            $validator = Validator::make($request->all(), [
                'profile_pic' => 'image|mimes:jpeg,png,jpg',
                'first_name' => 'required',
                'middle_name' => 'required',
                'last_name' => 'required',
                'mobile_no' => 'required|numeric|digits:10',
                'email' => 'required|email|unique:users',
                'dob' => 'required'
            ], $messages);
        }

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors(),'status'=>'failed']);
        }

        if(isset($request->action) && $request->action == "update"){
            $action = "update";
            $user = User::find($request->user_id);

            if(!$user){
                return response()->json(['status' => '400']);
            }

            $old_image = $user->profile_pic;
            $image_name = $old_image;
        }
        else{
            $action = "add";
            $user = new User();
            $user->role = 2;
            $user->estatus = 2;
            $user->created_at = new \DateTime(null, new \DateTimeZone('Asia/Kolkata'));
            $image_name=null;
        }

        $user->parent_id = $request->parentUser;
        $user->zone_id = $request->zoneDropdown;
        $user->first_name = $request->first_name;
        $user->middle_name = $request->middle_name;
        $user->last_name = $request->last_name;
        $user->mobile_no = $request->mobile_no;
        $user->email = $request->email;
        $user->gender = $request->gender;
        $user->birth_date = $request->dob;
        $user->address = $request->address;

        if ($request->hasFile('profile_pic')) {
            $image = $request->file('profile_pic');
            $image_name = 'profilePic_' . rand(111111, 999999) . time() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('images/profile_pic');
            $image->move($destinationPath, $image_name);
            if(isset($old_image)) {
                $old_image = public_path('images/profile_pic/' . $old_image);
                if (file_exists($old_image)) {
                    unlink($old_image);
                }
            }
            $user->profile_pic = $image_name;
        }

        $user->save();

        // if ($action=='add'){
        //     $project_page_ids1 = ProjectPage::where('parent_menu',0)->where('is_display_in_menu',0)->pluck('id')->toArray();
        //     $project_page_ids2 = ProjectPage::where('parent_menu',"!=",0)->where('is_display_in_menu',1)->pluck('id')->toArray();
        //     $project_page_ids = array_merge($project_page_ids1,$project_page_ids2);

        //     foreach ($project_page_ids as $pid) {
        //         $user_permission = new UserPermission();
        //         $user_permission->user_id = $user->id;
        //         $user_permission->project_page_id = $pid;
        //         $user_permission->save();
        //     }
        // }

        return response()->json(['status' => '200', 'action' => $action]);
    }

    public function alluserslist(Request $request){
        if ($request->ajax()) {
            $tab_type = $request->tab_type;
            if ($tab_type == "active_user_tab"){
                $estatus = 1;
            }
            elseif ($tab_type == "deactive_user_tab"){
                $estatus = 2;
            }

            $columns = array(
                0 => 'id',
                1 => 'profile_pic',
                2 => 'parent_profile',
                3 => 'contact_info',
                4 => 'other_info',
                5 => 'estatus',
                6 => 'created_at',
                7 => 'action',
            );

            $totalData = User::where('role',2);
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

            if($order == "profile_pic"){
                $order = "first_name";
            }

            if(empty($request->input('search.value')))
            {
                $users = User::where('role',2);
                if (isset($estatus)){
                    $users = $users->where('estatus',$estatus);
                }
                $users = $users->offset($start)
                    ->limit($limit)
                    ->orderBy($order,$dir)
                    ->get();
            }
            else {
                $search = $request->input('search.value');
                $users =  User::where('role',2);
                if (isset($estatus)){
                    $users = $users->where('estatus',$estatus);
                }
                $users = $users->where(function($query) use($search){
                      $query->where('id','LIKE',"%{$search}%")
                            ->orWhere('email', 'LIKE',"%{$search}%")
                            ->orWhere('mobile_no', 'LIKE',"%{$search}%")
                            ->orWhere('created_at', 'LIKE',"%{$search}%");
                      })
                      ->offset($start)
                      ->limit($limit)
                      ->orderBy($order,$dir)
                      ->get();

                $totalFiltered = User::where('role',2);
                if (isset($estatus)){
                    $totalFiltered = $totalFiltered->where('estatus',$estatus);
                }
                $totalFiltered = $totalFiltered->where(function($query) use($search){
                        $query->where('id','LIKE',"%{$search}%")
                            ->orWhere('email', 'LIKE',"%{$search}%")
                            ->orWhere('mobile_no', 'LIKE',"%{$search}%")
                            ->orWhere('created_at', 'LIKE',"%{$search}%");
                        })
                        ->count();
            }

            $data = array();

            if(!empty($users))
            {
                // $i=1;
                foreach ($users as $user)
                {
                    // $page_id = ProjectPage::where('route_url','admin.users.list')->pluck('id')->first();

                    $parentUserData = User::where('id', $user->parent_id)->get();
                    $parentName = "";
                    // if(!empty($parentUserData)){
                    //     $parentName = $parentUserData;
                    // }

                    if( $user->estatus == 1 && getUSerRole() == 1 ){
                        $estatus = '<label class="switch"><input type="checkbox" id="Userstatuscheck_'. $user->id .'" onchange="changeUserStatus('. $user->id .')" value="1" checked="checked"><span class="slider round"></span></label>';
                    }
                    elseif ($user->estatus==1){
                        $estatus = '<label class="switch"><input type="checkbox" id="Userstatuscheck_'. $user->id .'" value="1" checked="checked"><span class="slider round"></span></label>';
                    }

                    if( $user->estatus == 2 && getUSerRole() == 1 ){
                        $estatus = '<label class="switch"><input type="checkbox" id="Userstatuscheck_'. $user->id .'" onchange="changeUserStatus('. $user->id .')" value="2"><span class="slider round"></span></label>';
                    }
                    elseif ($user->estatus==2){
                        $estatus = '<label class="switch"><input type="checkbox" id="Userstatuscheck_'. $user->id .'" value="2"><span class="slider round"></span></label>';
                    }

                    if(isset($user->profile_pic) && $user->profile_pic != null){
                        $profile_pic = $user->profile_pic;
                    }
                    else{
                        $profile_pic = url('images/avatar.jpg');
                    }

                    $contact_info = '';
                    if (isset($user->email)){
                        $contact_info = '<span><i class="fa fa-envelope" aria-hidden="true"></i> ' .$user->email .'</span>';
                    }
                    if (isset($user->mobile_no)){
                        $contact_info .= '<span><i class="fa fa-phone" aria-hidden="true"></i> ' .$user->mobile_no .'</span>';
                    }

                    $other_info = '';
                    if ($user->gender == 2){
                        $other_info = '<span><i class="fa fa-male" aria-hidden="true"  style="font-size: 20px; margin-right: 5px"></i> Male</span>';
                    } else if($user->gender == 1){
                        $other_info = '<span><i class="fa fa-female" aria-hidden="true" style="font-size: 20px; margin-right: 5px"></i> Female</span>';
                    } else {
                        $other_info = '<span><i class="fa fa-male" aria-hidden="true" style="font-size: 20px; margin-right: 5px"></i> Other</span>';
                    }
                    if($user->birth_date != NULL){
                        $other_info .= '<span><i class="fa fa-birthday-cake" aria-hidden="true"></i> '.date('d-m-Y', strtotime($user->birth_date)).'</span>';
                    }

                    // if (isset($user->email)){
                    //     $login_info = '<span>' .$user->email .'</span>';
                    // }
                    // if (isset($user->password)){
                    //     $login_info .= '<span>' .$user->decrypted_password .'</span>';
                    // }

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

                    $action='';
                    if ( getUSerRole() == 1 ){
                        $action .= '<button id="editUserBtn" class="btn btn-gray text-blue btn-sm" data-toggle="modal" data-target="#UserModal" onclick="" data-id="' .$user->id. '"><i class="fa fa-pencil" aria-hidden="true"></i></button>';
                    }
                    if ( getUSerRole() == 1 ){
                        $action .= '<button id="deleteUserBtn" class="btn btn-gray text-danger btn-sm" data-toggle="modal" data-target="#DeleteUserModal" onclick="" data-id="' .$user->id. '"><i class="fa fa-trash-o" aria-hidden="true"></i></button>';
                    }

                    // $nestedData['id'] = $i;
                    $nestedData['profile_pic'] = '<img src="'. $profile_pic .'" width="40px" height="40px" alt="Profile Pic"><span class="ml-2">'.$full_name.'</span>';
                    $nestedData['parent_profile'] = $parentName;
                    $nestedData['contact_info'] = $contact_info;
                    $nestedData['other_info'] = $other_info;
                    $nestedData['estatus'] = $estatus;
                    $nestedData['created_at'] = date('d-m-Y h:i A', strtotime($user->created_at));
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

    public function changeuserstatus($id){
        $user = User::find($id);
        if ($user->estatus==1){
            $user->estatus = 2;
            $user->save();
            return response()->json(['status' => '200','action' =>'deactive']);
        }
        if ($user->estatus==2){
            $user->estatus = 1;
            $user->save();
            return response()->json(['status' => '200','action' =>'active']);
        }
    }

    public function edituser($id){
        $user = User::find($id);
        return response()->json($user);
    }

    public function deleteuser($id){
        $user = User::find($id);
        if ($user){
            $image = $user->profile_pic;
            $user->estatus = 3;
            $user->save();

            $user->delete();

            $image = public_path('images/profile_pic/' . $image);
            if (file_exists($image)) {
                unlink($image);
            }
            return response()->json(['status' => '200']);
        }
        return response()->json(['status' => '400']);
    }

    public function permissionuser($id){
        $user_permissions = UserPermission::where('user_id',$id)->orderBy('project_page_id','asc')->get();
        return view('admin.users.permission',compact('user_permissions'));
    }

    public function savepermission(Request $request){
        foreach ($request->permissionData as $pdata) {
            $user_permission = UserPermission::where('user_id',$request->user_id)->where('project_page_id',$pdata['page_id'])->first();
            $user_permission->can_read = $pdata['can_read'];
            $user_permission->can_write = $pdata['can_write'];
            $user_permission->can_delete = $pdata['can_delete'];
            $user_permission->save();
        }

        return response()->json(['status' => '200']);
    }
}
