<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Category;
use App\Models\CustomerDeviceToken;
use App\Models\Notification;
use App\Models\RequestKaryaKarta;
use App\Models\ {Zone, User,Settings,ProfessionalDetails,ContactUs};
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

        if($request->member_id == 0){
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

        }else{
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
                    return $query->where('role', 3)->where('id','!=',$request->member_id)->where('estatus','!=',3);
                })],
                'mobile_no' => ['required', 'numeric', 'digits:10',Rule::unique('users')->where(function ($query) use ($request) {
                    return $query->where('role', 3)->where('id','!=',$request->member_id)->where('estatus','!=',3);
                })],
            ], $messages); 
        }

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
            'family_member_id.required' =>'Please provide a Member Id',
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

        if($request->family_member_id == 0){
            if($request->email == "" && $request->mobile_no == ""){
                $validator = Validator::make($request->all(), [
                    'created_by_id' => 'required',
                    'family_member_id' => 'required',
                    'profile_pic' => 'image|mimes:jpeg,png,jpg',
                    'first_name' => 'required',
                    'middle_name' => 'required',
                    'last_name' => 'required',
                    'birth_date' => 'required',
                    'gender' => 'required',
                    'zone_id' => 'required',
                    'role' => 'required',
                    
                ], $messages);

            }elseif($request->email == "" && $request->mobile_no != ""){
                $validator = Validator::make($request->all(), [
                    'created_by_id' => 'required',
                    'family_member_id' => 'required',
                    'profile_pic' => 'image|mimes:jpeg,png,jpg',
                    'first_name' => 'required',
                    'middle_name' => 'required',
                    'last_name' => 'required',
                    'birth_date' => 'required',
                    'gender' => 'required',
                    'zone_id' => 'required',
                    'role' => 'required',
                    'mobile_no' => [ 'numeric', 'digits:10',Rule::unique('users')->where(function ($query) use ($request) {
                        return $query->where('estatus','!=',3);
                    })],
                ], $messages);
            }elseif($request->email != "" && $request->mobile_no == ""){
                $validator = Validator::make($request->all(), [
                    'created_by_id' => 'required',
                    'family_member_id' => 'required',
                    'profile_pic' => 'image|mimes:jpeg,png,jpg',
                    'first_name' => 'required',
                    'middle_name' => 'required',
                    'last_name' => 'required',
                    'birth_date' => 'required',
                    'gender' => 'required',
                    'zone_id' => 'required',
                    'role' => 'required',
                    'email' => [ 'string', 'email', 'max:191',Rule::unique('users')->where(function ($query) use ($request) {
                        return $query->where('role', 3)->where('estatus','!=',3);
                    })],
                   
                ], $messages);
            }else{

                $validator = Validator::make($request->all(), [
                    'created_by_id' => 'required',
                    'family_member_id' => 'required',
                    'profile_pic' => 'image|mimes:jpeg,png,jpg',
                    'first_name' => 'required',
                    'middle_name' => 'required',
                    'last_name' => 'required',
                    'birth_date' => 'required',
                    'gender' => 'required',
                    'zone_id' => 'required',
                    'role' => 'required',
                    'email' => [ 'string', 'email', 'max:191',Rule::unique('users')->where(function ($query) use ($request) {
                        return $query->where('id','!=',$request->family_member_id)->where('estatus','!=',3);
                    })],
                    'mobile_no' => ['numeric', 'digits:10',Rule::unique('users')->where(function ($query) use ($request) {
                        return $query->where('estatus','!=',3);
                    })],
                ], $messages);

            }
        }else{
            if($request->email == "" && $request->mobile_no == ""){
                $validator = Validator::make($request->all(), [
                    'created_by_id' => 'required',
                    'family_member_id' => 'required',
                    'profile_pic' => 'image|mimes:jpeg,png,jpg',
                    'first_name' => 'required',
                    'middle_name' => 'required',
                    'last_name' => 'required',
                    'birth_date' => 'required',
                    'gender' => 'required',
                    'zone_id' => 'required',
                    'role' => 'required',
                ], $messages);

            }elseif($request->email == "" && $request->mobile_no != ""){
                $validator = Validator::make($request->all(), [
                    'created_by_id' => 'required',
                    'family_member_id' => 'required',
                    'profile_pic' => 'image|mimes:jpeg,png,jpg',
                    'first_name' => 'required',
                    'middle_name' => 'required',
                    'last_name' => 'required',
                    'birth_date' => 'required',
                    'gender' => 'required',
                    'zone_id' => 'required',
                    'role' => 'required',
                    
                    'mobile_no' => [ 'numeric', 'digits:10',Rule::unique('users')->where(function ($query) use ($request) {
                        return $query->where('id','!=',$request->family_member_id)->where('estatus','!=',3);
                    })],
                ], $messages);
            }elseif($request->email != "" && $request->mobile_no == ""){
                $validator = Validator::make($request->all(), [
                    'created_by_id' => 'required',
                    'family_member_id' => 'required',
                    'profile_pic' => 'image|mimes:jpeg,png,jpg',
                    'first_name' => 'required',
                    'middle_name' => 'required',
                    'last_name' => 'required',
                    'birth_date' => 'required',
                    'gender' => 'required',
                    'zone_id' => 'required',
                    'role' => 'required',
                    'email' => [ 'string', 'email', 'max:191',Rule::unique('users')->where(function ($query) use ($request) {
                        return $query->where('id','!=',$request->family_member_id)->where('estatus','!=',3);
                    })],
                    
                ], $messages);
            }else{

                $validator = Validator::make($request->all(), [
                    'created_by_id' => 'required',
                    'family_member_id' => 'required',
                    'profile_pic' => 'image|mimes:jpeg,png,jpg',
                    'first_name' => 'required',
                    'middle_name' => 'required',
                    'last_name' => 'required',
                    'birth_date' => 'required',
                    'gender' => 'required',
                    'zone_id' => 'required',
                    'role' => 'required',
                    'email' => [ 'string', 'email', 'max:191',Rule::unique('users')->where(function ($query) use ($request) {
                        return $query->where('id','!=',$request->family_member_id)->where('estatus','!=',3);
                    })],
                    'mobile_no' => [ 'numeric', 'digits:10',Rule::unique('users')->where(function ($query) use ($request) {
                        return $query->where('id','!=',$request->family_member_id)->where('estatus','!=',3);
                    })],
                ], $messages);

            } 
        }

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), "Validation Errors", []);
        }
        if($request->family_member_id == 0){
           $user = new User();
        }else{
           $user = User::find($request->family_member_id);
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

        $device = CustomerDeviceToken::where('user_id',$request->user_id)->where('device_type',$request->device_type)->first();
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

        $data = new UserResource($user);
       
        return $this->sendResponseWithData($data,"Device Token updated.");
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

        $notifications = Notification::where('user_id',$request->user_id)->orderBy('created_at','DESC')->get();
        $notifications_arr = array();
        foreach ($notifications as $notification){
            $temp = array();
            $temp['id'] = $notification->id;
            $temp['title'] = $notification->notify_title;
            $temp['desc'] = $notification->notify_desc;
            $temp['image'] = isset($notification->notify_thumb)?$notification->notify_thumb:"";
            $temp['value_id'] = $notification->value_id;
            $temp['type'] = $notification->type;
            $temp['created_at'] = $notification->created_at;
            array_push($notifications_arr,$temp);
        }

        return $this->sendResponseWithData($notifications_arr,"Notifications Retrieved Successfully.");
    }


    public function settings(){
        
        $Setting = Settings::first();
        $data['company_name'] = $Setting->company_name;
        $data['email'] = $Setting->email;
        $data['mobile_no'] = $Setting->mobile_no;
        $data['company_logo'] = isset($Setting->company_logo)?url('images/company/'.$Setting->company_logo):"";
        $data['company_favicon'] = isset($Setting->company_favicon)?url('images/company/'.$Setting->company_favicon):"";
          
        return $this->sendResponseWithData($data,"Setting Data Retrieved Successfully.");
    }

    public function add_edit_professional(Request $request){
        $messages = [
            'user_id.required' =>'Please provide a User Id',   
        ];

        $validator = Validator::make($request->all(), [
            'user_id' => 'required', 
        ], $messages);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), "Validation Errors", []);
        }

        $user = User::where('id',$request->user_id)->where('estatus',1)->first();
        if (!$user){
            return $this->sendError("User Not Exist", "Not Found Error", []);
        }


        $details = ProfessionalDetails::where('user_id',$request->user_id)->first();
        if($details){
           $details = ProfessionalDetails::find($details->id);
        }else{
            $details = new ProfessionalDetails();
        }
        $details->user_id = $request->user_id;
        $details->type = $request->type;
        $details->title = $request->title;
        $details->education = $request->education;
        $details->address = $request->address;
        $details->save();
        return $this->sendResponseWithData($details,"Updated Professional Data Successfully");
    }

    public function getProfessionalDetails($id){

        $user = User::where('id',$id)->where('estatus',1)->first();
        if (!$user){
            return $this->sendError("User Not Exist", "Not Found Error", []);
        }

        $ProfessionalDetails = ProfessionalDetails::where('user_id',$id)->first();
        return $this->sendResponseWithData($ProfessionalDetails,"Professional Details Retrieved Successfully.");
    }

    public function contact(Request $request){
        $messages = [
            'user_id.required' =>'Please provide a User Id',   
            'message.required' =>'Please provide a Message',   
        ];

        $validator = Validator::make($request->all(), [
            'user_id' => 'required', 
            'message' => 'required', 
        ], $messages);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), "Validation Errors", []);
        }

        $user = User::where('id',$request->user_id)->where('estatus',1)->first();
        if (!$user){
            return $this->sendError("User Not Exist", "Not Found Error", []);
        }

        $contact = new ContactUs();
        $contact->user_id = $request->user_id;
        $contact->message = $request->message;
        $contact->save();
        return $this->sendResponseWithData($contact,"Add Contact Successfully");
    }

    public function request_karyakarta(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'request_by_user_id' => 'required',
        ]);

        if($validator->fails()){
            return $this->sendError($validator->errors(), "Validation Errors", []);
        }

        $user = User::where('id',$request->user_id)->where('estatus',1)->first();
        if (!$user){
            return $this->sendError("User Not Exist", "Not Found Error", []);
        }
        $KaryaKarta =  RequestKaryaKarta::where('user_id',$request->user_id)->where('estatus',1)->first();
        if(!$KaryaKarta){
            $requestkaryakarta = New RequestKaryaKarta();
            $requestkaryakarta->user_id = $request->user_id;
            $requestkaryakarta->request_by_user_id = $request->request_by_user_id;
            $requestkaryakarta->date_time = new \DateTime(null, new \DateTimeZone('Asia/Kolkata'));
            $requestkaryakarta->created_at = new \DateTime(null, new \DateTimeZone('Asia/Kolkata'));
            $requestkaryakarta->save();
        }
      
        return $this->sendResponseSuccess("Send Request Successfully.");
    }

   
}
