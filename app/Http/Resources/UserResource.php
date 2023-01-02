<?php

namespace App\Http\Resources;

use App\Models\RequestKaryaKarta;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $requestkaryakarta = RequestKaryaKarta::where('user_id',$this->id)->latest('id')->first();
        $request_karyakarta_status = 0;
        if($requestkaryakarta){
            $request_karyakarta_status = $requestkaryakarta->estatus;
        }
        return [
            'user_id' => $this->id,
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'profile_pic' => isset($this->profile_pic) ? $this->profile_pic : asset('images/default_avatar.jpg'),
            'email' => $this->email,
            'mobile_no' => $this->mobile_no,
            'address' => $this->address,
            'birth_date' => $this->birth_date,
            'gender' => $this->gender,
            'zone_id' => $this->zone_id,
            'parent_id' => $this->parent_id,
            'family_parent_id' => $this->family_parent_id,
            'status' => ($this->role == 2)?"Karykarta":"Haribhagat",
            'role' => ($this->role == 2)?1:2,
            'request_karyakarta' => $request_karyakarta_status,
        ];
    }
}
