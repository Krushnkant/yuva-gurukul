<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends BaseController
{

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile_no' => 'required'
        ]);

        if($validator->fails()){
            return $this->sendError($validator->errors(), "Validation Errors", []);
        }

        $mobile_no = $request->mobile_no;
        $user = User::where('mobile_no',$mobile_no)->first();
        if ($user){
            if($user->estatus != 1){
                return $this->sendError("Your account is de-activated by admin.", "Account De-active", []);
            }
            $data['otp'] =  mt_rand(100000,999999);
            $user->otp = $data['otp'];
            $user->save();
            
            $final_data = array();
            array_push($final_data,$data);

            //send_sms($mobile_no, $data['otp']);
            return $this->sendResponseWithData($final_data, 'User login successfully.');
        }else{
            $data['otp'] =  mt_rand(100000,999999);
            $user = new User();
            $user->mobile_no = $mobile_no;
            $user->otp = $data['otp'];
            $user->save();
            $final_data = array();
            array_push($final_data,$data);
            //send_sms($mobile_no, $data['otp']);
            return $this->sendResponseWithData($final_data, 'User registered successfully.');
        }
    }

}
