<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Category;
use App\Models\CustomerDeviceToken;
use App\Models\Notification;
use App\Models\ProductVariant;
use App\Models\ {Zone, User};
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends BaseController
{
    public function verify_otp(Request $request){
        $validator = Validator::make($request->all(), [
            'mobile_no' => 'required',
            'otp' => 'required'
        ]);

        if($validator->fails()){
            return $this->sendError($validator->errors(), "Validation Errors", []);
        }

        $user = User::where('mobile_no',$request->mobile_no)->where('otp',$request->otp)->where('estatus',1)->first();

        if ($user){
            $user->otp = null;
            $user->is_verify = 1;
            $user->save();
            $data['profile_data'] =  new UserResource($user);
            $data['token'] =  $user->createToken('MyApp')->accessToken;
            $final_data = array();
            array_push($final_data,$data);
            return $this->sendResponseWithData($final_data,'OTP verified successfully.');
        }
        else{
            return $this->sendError('OTP verification Failed.', "verification Failed", []);
        }
    }

    public function send_otp(Request $request){
        $validator = Validator::make($request->all(), [
            'mobile_no' => 'required',
        ]);

        if($validator->fails()){
            return $this->sendError($validator->errors(), "Validation Errors", []);
        }

        $user = User::where('mobile_no',$request->mobile_no)->where('estatus',1)->first();
        if (!$user){
            return $this->sendError("User Not Exist", "Not Found Error", []);
        }

        $data = array();
        $otp['otp'] =  mt_rand(1000,9999);
        send_sms($request->mobile_no, $otp['otp']);

        array_push($data,$otp);
        $user->otp = $otp['otp'];
        $user->save();
        return $this->sendResponseWithData($data, "User OTP generated.");
    }

    public function edit_profile(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'first_name' => 'required',
            'middle_name' => 'required',
            'last_name' => 'required',
            'zone_id' => 'required',
            'gender' => 'required',
            'birth_date' => 'required',
            'email' => ['required', 'string', 'email', 'max:191',Rule::unique('users')->where(function ($query) use ($request) {
                return $query->where('role', 3)->where('id','!=',$request->user_id)->where('estatus','!=',3);
            })],
            

        ]);

        if($validator->fails()){
            return $this->sendError($validator->errors(), "Validation Errors", []);
        }

        $user = User::find($request->user_id);
        if (!$user)
        {
            return $this->sendError('User Not Exist.', "Not Found Error", []);
        }
        $user->first_name = $request->first_name;
        $user->middle_name = $request->middle_name;
        $user->last_name = $request->last_name;
        $user->birth_date = $request->birth_date;
        $user->zone_id = $request->zone_id;
        $user->role = 3;
        if (isset($request->gender)) {
            $user->gender = $request->gender;
        }
        if($user->parent_id == 0){
            if($user->gender == 1){
                $user->parent_id = 3;
            }else{
                $user->parent_id = 2;
            }
        }
        $user->email = isset($request->email) ? $request->email : null;

        if ($request->hasFile('profile_pic')) {
            if(isset($user->profile_pic)) {
                $old_image = public_path('images/profile_pic/' . $user->profile_pic);
                if (file_exists($old_image)) {
                    unlink($old_image);
                }
            }

            $image = $request->file('profile_pic');
            $ext = $image->getClientOriginalExtension();
            $ext = strtolower($ext);
            // $all_ext = array("png","jpg", "jpeg", "jpe", "jif", "jfif", "jfi","tiff","tif","raw","arw","svg","svgz","bmp", "dib","mpg","mp2","mpeg","mpe");
            $all_ext = array("png", "jpg", "jpeg");
            if (!in_array($ext, $all_ext)) {
                return $this->sendError('Invalid type of image.', "Extension error", []);
            }

            $image_name = 'profilePic_' . rand(111111, 999999) . time() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('images/profile_pic');
            $image->move($destinationPath, $image_name);
            $user->profile_pic = $image_name;
        }
        $user->save();

        return $this->sendResponseWithData(new UserResource($user),'User profile updated successfully.');
    }


    public function view_profile(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
        ]);
        if($validator->fails()){
            return $this->sendError($validator->errors(), "Validation Errors", []);
        }
        $user = User::where('id',$request->user_id)->where('estatus',1)->first();

        if(!$user){
            return $this->sendError("You can not view this profile", "Invalid user", []);
        }
        $data = array();
        array_push($data,new UserResource($user));
        // array_push($data, new UserResource($isPayTm));
        return $this->sendResponseWithData($data, 'User profile Retrieved successfully.');
    }


    public function add_edit_member(Request $request){
        $messages = [
            'created_by_id.required' =>'Please provide a Created By Id',
            'member_id.required' =>'Please provide a Member Id',
            'profile_pic.image' =>'Please provide a Valid Extension Image(e.g: .jpg .png)',
            'profile_pic.mimes' =>'Please provide a Valid Extension Image(e.g: .jpg .png)',
            'first_name.required' =>'Please provide a First Name',
            'middle_name.required' =>'Please provide a Middle Name',
            'last_name.required' =>'Please provide a Last Name',
            'birth_date.required' =>'Please provide a Date of Birth.',
            'email.required' =>'Please provide a e-mail address.',
            'gender.required' =>'Please provide a gender.',
            'zone_id.required' =>'Please provide a Zone Id.',
            'role.required' =>'Please provide a role.',
        ];

        $validator = Validator::make($request->all(), [
            'created_by_id' => 'required',
            'member_id' => 'required',
            'profile_pic' => 'image|mimes:jpeg,png,jpg',
            'first_name' => 'required',
            'middle_name' => 'required',
            'last_name' => 'required',
            'birth_date' => 'required',
            'gender' => 'required',
            'zone_id' => 'required',
            'role' => 'required',
            'email' => ['required', 'string', 'email', 'max:191',Rule::unique('users')->where(function ($query) use ($request) {
                return $query->where('estatus','!=',3);
            })],
            'mobile_no' => ['required', 'numeric', 'digits:10',Rule::unique('users')->where(function ($query) use ($request) {
                return $query->where('estatus','!=',3);
            })],
        ], $messages);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), "Validation Errors", []);
        }
        if($request->member_id == 0){
           $user = new User();
        }else{
           $user = User::find($request->member_id);
        }
        $user->first_name = $request->first_name;
        $user->middle_name = $request->middle_name;
        $user->last_name = $request->last_name;
        $user->mobile_no = $request->mobile_no;
        $user->gender = $request->gender;
        $user->birth_date = $request->birth_date;
        $user->email = $request->email;
        $user->role = $request->role;
        $user->zone_id = $request->zone_id;
        $user->parent_id = $request->created_by_id;
        $user->created_at = new \DateTime(null, new \DateTimeZone('Asia/Kolkata'));

        if ($request->hasFile('profile_pic')) {
            $image = $request->file('profile_pic');
            $image_name = 'profilePic_' . rand(111111, 999999) . time() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('images/profile_pic');
            $image->move($destinationPath, $image_name);
            $user->profile_pic = $image_name;
        }

        $user->save();
        return $this->sendResponseSuccess("Add Member Successfully");
    }

    public function getMembers($id){
        $members = User::where('parent_id',$id)->where('estatus',1)->get();
        $members_arr = array();
        foreach ($members as $member){
            array_push($members_arr,new UserResource($member));
        }

        return $this->sendResponseWithData($members_arr,"Members Retrieved Successfully.");
    }

    public function removeMember($id){
        $member = User::find($id);
        if(!$member){
            return $this->sendError("You can not find member", "Invalid member", []);
        }
        $member->estatus = 3;
        $member->save();
        $member->delete();
        return $this->sendResponseSuccess("Member Removed Successfully.");
    }

    public function getZone(){
        $zones = Zone::where('estatus',1)->orderBy('created_at','ASC')->get();
        $zones_arr = array();
        foreach ($zones as $zone){
            $temp = array();
            $temp['id'] = $zone->id;
            $temp['name'] = $zone->name;
            array_push($zones_arr,$temp);
        }

        return $this->sendResponseWithData($zones_arr,"Zone Retrieved Successfully.");
    }

    public function add_edit_member_family(Request $request){
        $messages = [
            'created_by_id.required' =>'Please provide a Created By Id',
            'member_id.required' =>'Please provide a Member Id',
            'profile_pic.image' =>'Please provide a Valid Extension Image(e.g: .jpg .png)',
            'profile_pic.mimes' =>'Please provide a Valid Extension Image(e.g: .jpg .png)',
            'first_name.required' =>'Please provide a First Name',
            'middle_name.required' =>'Please provide a Middle Name',
            'last_name.required' =>'Please provide a Last Name',
            'birth_date.required' =>'Please provide a Date of Birth.',
           // 'email.required' =>'Please provide a e-mail address.',
            'gender.required' =>'Please provide a gender.',
            'zone_id.required' =>'Please provide a Zone Id.',
            'role.required' =>'Please provide a role.',
        ];

        $validator = Validator::make($request->all(), [
            'created_by_id' => 'required',
            'member_id' => 'required',
            'profile_pic' => 'image|mimes:jpeg,png,jpg',
            'first_name' => 'required',
            'middle_name' => 'required',
            'last_name' => 'required',
            'birth_date' => 'required',
            'gender' => 'required',
            'zone_id' => 'required',
            'role' => 'required',
            'email' => [ 'string', 'email', 'max:191',Rule::unique('users')->where(function ($query) use ($request) {
                return $query->where('estatus','!=',3);
            })],
            'mobile_no' => [ 'numeric', 'digits:10',Rule::unique('users')->where(function ($query) use ($request) {
                return $query->where('estatus','!=',3);
            })],
        ], $messages);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), "Validation Errors", []);
        }
        if($request->member_id == 0){
           $user = new User();
        }else{
           $user = User::find($request->member_id);
        }
        $user->first_name = $request->first_name;
        $user->middle_name = $request->middle_name;
        $user->last_name = $request->last_name;
        $user->mobile_no = $request->mobile_no;
        $user->gender = $request->gender;
        $user->birth_date = $request->birth_date;
        $user->email = $request->email;
        $user->role = $request->role;
        $user->zone_id = $request->zone_id;
        $user->parent_id = $request->created_by_id;
        $user->family_parent_id = $request->created_by_id;
        $user->created_at = new \DateTime(null, new \DateTimeZone('Asia/Kolkata'));

        if ($request->hasFile('profile_pic')) {
            $image = $request->file('profile_pic');
            $image_name = 'profilePic_' . rand(111111, 999999) . time() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('images/profile_pic');
            $image->move($destinationPath, $image_name);
            $user->profile_pic = $image_name;
        }

        $user->save();
        return $this->sendResponseSuccess("Add Member Successfully");
    }

    public function getMemberFamily($id){
        $members = User::where('family_parent_id',$id)->where('estatus',1)->get();
        $members_arr = array();
        foreach ($members as $member){
            array_push($members_arr,new UserResource($member));
        }

        return $this->sendResponseWithData($members_arr,"Members Retrieved Successfully.");
    }

    public function removeMemberFamily($id){
        $member = User::find($id);
        if(!$member){
            return $this->sendError("You can not find member", "Invalid member", []);
        }
        $member->estatus = 3;
        $member->save();
        $member->delete();
        return $this->sendResponseSuccess("Member Removed Successfully.");
    }


    public function update_token(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'token' => 'required',
            'device_type' => 'required',
        ]);

        if($validator->fails()){
            return $this->sendError($validator->errors(), "Validation Errors", []);
        }

        $user = User::where('id',$request->user_id)->where('estatus',1)->first();
        if (!$user){
            return $this->sendError("User Not Exist", "Not Found Error", []);
        }

        $device = CustomerDeviceToken::where('user_id',$request->user_id)->first();
        if ($device){
            $device->token = $request->token;
            $device->device_type = $request->device_type;
        }
        else{
            $device = new CustomerDeviceToken();
            $device->user_id = $request->user_id;
            $device->token = $request->token;
            $device->device_type = $request->device_type;
        }
        $device->save();

        return $this->sendResponseSuccess("Device Token updated.");
    }

    public function notifications(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
        ]);

        if($validator->fails()){
            return $this->sendError($validator->errors(), "Validation Errors", []);
        }

        $user = User::where('id',$request->user_id)->where('estatus',1)->where('role',3)->first();
        if (!$user){
            return $this->sendError("User Not Exist", "Not Found Error", []);
        }

        $notifications = Notification::with('applicationdropdown')->whereIn('user_id',[0,$request->user_id])->orderBy('created_at','DESC')->get();
        $notifications_arr = array();
        foreach ($notifications as $notification){
            $temp = array();
            $temp['id'] = $notification->id;
            $temp['title'] = $notification->notify_title;
            $temp['desc'] = $notification->notify_desc;
            $temp['image'] = isset($notification->notify_thumb)?'public/'.$notification->notify_thumb:null;
            $temp['application_dropdown_id'] = isset($notification->application_dropdown_id)?$notification->application_dropdown_id:0;
            $temp['application_dropdown'] = isset($notification->application_dropdown_id)?$notification->applicationdropdown->title:null;

            if($notification->application_dropdown_id == 5){
                $category = Category::where('id',$notification->parent_value)->first();
                $product = ProductVariant::where('id',$notification->value)->pluck('product_title')->first();
                $temp['value_id'] = $notification->value;
                $temp['value_title'] = $product;
            }
            elseif($notification->application_dropdown_id == 7){
                $category = Category::where('id',$notification->value)->first();
                $temp['value_id'] = $category->id;
                $temp['value_title'] = $category->category_name;
            }
            else{
                $temp['value_id'] = null;
                $temp['value_title'] = $notification->value;
            }

            $temp['type'] = $notification->type;
            array_push($notifications_arr,$temp);
        }

        return $this->sendResponseWithData($notifications_arr,"Notifications Retrieved Successfully.");
    }

   
}
