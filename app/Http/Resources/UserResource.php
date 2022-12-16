<?php

namespace App\Http\Resources;

use App\Models\Category;
use App\Models\Settings;
use App\Models\UserCoverPhotos;
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
        ];
    }
}
