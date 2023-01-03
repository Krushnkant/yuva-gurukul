<?php

use App\Models\Attribute;
use App\Models\Category;
use App\Models\Level;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductAttribute;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cookie;

function getLeftMenuPages(){
    $pages = \App\Models\ProjectPage::where('parent_menu',0)->orderBy('sr_no','ASC')->get()->toArray();
    return $pages;
}

function getUSerRole(){
    return  \Illuminate\Support\Facades\Auth::user()->role;
}

function is_write($page_id){
    $is_write = \App\Models\UserPermission::where('user_id',\Illuminate\Support\Facades\Auth::user()->id)->where('project_page_id',$page_id)->where('can_write',1)->first();
    if ($is_write){
        return true;
    }
    return false;
}

function is_delete($page_id){
    $is_delete = \App\Models\UserPermission::where('user_id',\Illuminate\Support\Facades\Auth::user()->id)->where('project_page_id',$page_id)->where('can_delete',1)->first();
    if ($is_delete){
        return true;
    }
    return false;
}

function is_read($page_id){
    $is_read = \App\Models\UserPermission::where('user_id',\Illuminate\Support\Facades\Auth::user()->id)->where('project_page_id',$page_id)->where('can_read',1)->first();
    if ($is_read){
        return true;
    }
    return false;
}

function UploadImage($image, $path){
    $imageName = Str::random().'.'.$image->getClientOriginalExtension();
    $path = $image->move(public_path($path), $imageName);
    if($path == true){
        return $imageName;
    }else{
        return null;
    }
}

function get_required_variations($cat_id){
    $category = Category::where('id',$cat_id)->first()->toArray();
    $required_variations = array();
    $required_variation_ids = array();
    if ($category['attribute_id_variation']!=null) {
        $varis= explode(",", $category['attribute_id_variation']);
        foreach ($varis as $vari) {
            $spec = Attribute::with('attributeterm')->where('id', $vari)->where('is_specification', 0)->first()->toArray();
            if (isset($spec['attributeterm']) && !empty($spec['attributeterm'])) {
                array_push($required_variations, $spec);
                array_push($required_variation_ids, $spec['id']);
            }
        }
    }

    return ['required_variations'=>$required_variations, 'required_variation_ids'=>$required_variation_ids];
}

function get_required_variations_attribute($p_id){
    $productattributes = ProductAttribute::where('product_u_id',$p_id)->where('use_variation',1)->where('use_comman',0)->get()->toArray();
    $required_variations = array();
    $required_variation_ids = array();
    
    foreach ($productattributes as $req) {
        $term_ids = explode(',',$req['terms_id']);
        $spec = Attribute::with(['attributeterm' => function($q) use($term_ids ){
            $q->wherein('attribute_terms.id', $term_ids);
        }] )->where('id', $req['attribute_id'])->first()->toArray();
        if (isset($spec['attributeterm']) && !empty($spec['attributeterm'])) {
            array_push($required_variations, $spec);
            array_push($required_variation_ids, $spec['id']);
        }
    }
     

    return ['required_variations'=>$required_variations, 'required_variation_ids'=>$required_variation_ids];
}

function get_required_specifications($cat_id){
    $category = Category::where('id',$cat_id)->first()->toArray();
    $required_specifications = array();
    $required_specification_ids = array();
    if ($category['attribute_id_req_spec']!=null) {
        $req_spec = explode(",", $category['attribute_id_req_spec']);
        foreach ($req_spec as $req) {
            $spec = Attribute::with('attributeterm')->where('id', $req)->where('is_specification', 1)->first()->toArray();
            if (isset($spec['attributeterm']) && !empty($spec['attributeterm'])) {
                array_push($required_specifications, $spec);
                array_push($required_specification_ids, $spec['id']);
            }
        }
    }

    return ['required_specifications'=>$required_specifications, 'required_specification_ids'=>$required_specification_ids];
}

function get_optional_specifications($cat_id){
    $category = Category::where('id',$cat_id)->first()->toArray();
    $optional_specifications = array();
    if ($category['attribute_id_opt_spec']!=null) {
        $opt_spec = explode(",", $category['attribute_id_opt_spec']);
        foreach ($opt_spec as $opt) {
            $spec = Attribute::with('attributeterm')->where('id', $opt)->where('is_specification', 1)->first()->toArray();
            if (isset($spec['attributeterm']) && !empty($spec['attributeterm'])) {
                array_push($optional_specifications, $spec);
            }
        }
    }

    return $optional_specifications;
}

function VariantsList($variant_id="",$limit="",$per_page="",$user_id="",$price="",$arrival_days="",$is_wishlist=false){
    $variants = \App\Models\ProductVariant::with('attribute_term','product_variant_specification.attribute','product_variant_specification.attribute_term')->where('estatus',1);
    if (isset($variant_id) && $variant_id!=""){
        $variants = $variants->where('id',$variant_id);
    }

    if (isset($price) && $price!=""){
        $variants = $variants->where('sale_price',"<",$price);
    }

    if (isset($is_wishlist) && $is_wishlist==true && $user_id != 0 && $user_id != ""){
        $variants = $variants->whereHas('wishlist',function ($Query) use($user_id) {
                        $Query->where('user_id', $user_id);
                    });
    }

    if (isset($limit)&& $limit!=""){
        $variants = $variants->limit($limit);
    }

    $variants = $variants->orderBy('created_at','desc');

    if (isset($per_page)&& $per_page!=""){
        $variants = $variants->paginate($per_page);
    }
    else{
        $variants = $variants->get();
    }

//    dd($variants->toArray());
    $variants_arr = array();
    foreach ($variants as $variant){
        $diff_in_days = "";
        if (isset($arrival_days) && $arrival_days!=""){
            $to = \Carbon\Carbon::createFromFormat('Y-m-d H:s:i', $variant->created_at);
            $from = \Carbon\Carbon::createFromFormat('Y-m-d H:s:i', now());
            $diff_in_days = $to->diffInDays($from);
        }

        if ($diff_in_days == "" || $diff_in_days <= $arrival_days) {
            $temp = array();
            $temp['variant_id'] = $variant->id;
            $temp['product_id'] = $variant->product_id;
            $temp['product_title'] = $variant->product_title;
            $temp['images'] = $variant->variant_images;
            $temp['regular_price'] = $variant->regular_price;
            $temp['sale_price'] = $variant->sale_price;
            $temp['stock'] = $variant->stock;
            $temp['auto_discount_rs'] = $variant->auto_discount_rs;
            $temp['auto_discount_percent'] = $variant->auto_discount_percent;
            $temp['sale_price_for_premium_member'] = $variant->sale_price_for_premium_member;

            $temp['specifications'] = array();
            foreach ($variant->product_variant_specification as $product_variant_specification) {
                $temp_specification = array();
                $temp_specification['specification_title'] = $product_variant_specification->attribute->attribute_name;
                $temp_specification['specification_value'] = $product_variant_specification->attribute_term->attrterm_name;
                $temp_specification['specification_image'] = isset($product_variant_specification->attribute_term->attrterm_thumb) ? 'public/images/attrTermThumb/' . $product_variant_specification->attribute_term->attrterm_thumb : null;
                array_push($temp['specifications'], $temp_specification);
            }

            $temp['attribute'] = array();
            $attrid_for_variation = \App\Models\Product::with('attribute')->where('id', $variant->product_id)->first();
            $temp['attribute']['attribute_title'] = $attrid_for_variation->attribute->attribute_name;
            $temp['attribute']['attribute_value'] = $variant->attribute_term->attrterm_name;

            if ($user_id != 0 && $user_id != "") {
                $qty_in_cart = \App\Models\Cart::where('user_id', $user_id)->where('product_variant_id', $variant->id)->where('product_id', $variant->product_id)->where('estatus', 1)->pluck('quantity')->first();
                $temp['qty_in_cart'] = $qty_in_cart;

                $wishlist = \App\Models\Wishlist::where('user_id', $user_id)->where('product_variant_id', $variant->id)->where('product_id', $variant->product_id)->where('estatus', 1)->first();
                if ($wishlist) {
                    $temp['is_in_wishlist'] = true;
                } else {
                    $temp['is_in_wishlist'] = false;
                }
            }

            array_push($variants_arr, $temp);
        }
    }

    $data['variants'] = $variants_arr;
    if (isset($per_page)&& $per_page!=""){
        $data['total_records'] = $variants->toArray()['total'];
    }
    return $data;
}

function product_variant_detail($product_variant,$user_id=""){
    $temp = array();
    $temp['variant_id'] = $product_variant->id;
    $temp['product_id'] = $product_variant->product_id;
    $temp['product_title'] = $product_variant->product_title;
    $temp['images'] = $product_variant->variant_images;
    $temp['regular_price'] = $product_variant->regular_price;
    $temp['sale_price'] = $product_variant->sale_price;
    $temp['stock'] = $product_variant->stock;
    $temp['auto_discount_rs'] = $product_variant->auto_discount_rs;
    $temp['auto_discount_percent'] = $product_variant->auto_discount_percent;
    $temp['sale_price_for_premium_member'] = $product_variant->sale_price_for_premium_member;

    $temp['specifications'] = array();
    foreach ($product_variant->product_variant_specification as $product_variant_specification){
        $temp_specification = array();
        $temp_specification['specification_title'] = $product_variant_specification->attribute->attribute_name;
        $temp_specification['specification_value'] = $product_variant_specification->attribute_term->attrterm_name;
        $temp_specification['specification_image'] = isset($product_variant_specification->attribute_term->attrterm_thumb) ? 'public/images/attrTermThumb/'.$product_variant_specification->attribute_term->attrterm_thumb : null;
        array_push($temp['specifications'],$temp_specification);
    }

    $temp['attribute'] = array();
    $attrid_for_variation = \App\Models\Product::with('attribute')->where('id',$product_variant->product_id)->first();
    $temp['attribute']['attribute_title'] = $attrid_for_variation->attribute->attribute_name;
    $temp['attribute']['attribute_value'] = $product_variant->attribute_term->attrterm_name;

    if($user_id!=0 && $user_id!="") {
        $qty_in_cart = \App\Models\Cart::where('user_id',$user_id)->where('product_variant_id',$product_variant->id)->where('product_id',$product_variant->product_id)->where('estatus',1)->pluck('quantity')->first();
        $temp['qty_in_cart'] = $qty_in_cart;

        $wishlist = \App\Models\Wishlist::where('user_id',$user_id)->where('product_variant_id',$product_variant->id)->where('product_id',$product_variant->product_id)->where('estatus',1)->first();
        if ($wishlist){
            $temp['is_in_wishlist'] = true;
        }else{
            $temp['is_in_wishlist'] = false;
        }
    }

    return $temp;
}

function category_detail($category){
    $temp = array();
    $temp['id'] = $category['id'];
    $temp['category_name'] = $category['category_name'];
    $temp['category_thumb'] = 'public/'.$category['category_thumb'];
    $temp['total_products'] = $category['total_products'];
    return $temp;
}

function getDropdownInfoVal($Info){
    $categories = Category::where('estatus',1)->orderBy('created_at','DESC')->get();
    $html = '';
 

    if ($Info == 2){
        $html .= '<div class="form-group">
                    <label class="col-form-label" for="category">Select Category
                    </label>
                    <select id="value" name="value" class="category_dropdown_catalog">
                        <option></option>
                    </select>
                    <label id="value-error" class="error invalid-feedback animated fadeInDown" for="value"></label>
                    </div>';
    }

    if ($Info == 3){
        $html .= '<div class="form-group" id="category_dropdown">
                    <label class="col-form-label" for="category">Select Category
                    </label>
                    <select id="value" name="value" class="">
                        <option></option>
                    </select>
                    <label id="value-error" class="error invalid-feedback animated fadeInDown" for="value"></label>
                    </div>';
    }

  

    if ($Info == 4){
        $html .= '<div class="form-group">
                    <label class="col-form-label" for="bannerUrl">Banner URL</label>
                    <input type="text" class="form-control input-flat" id="value" name="value" value="">
                    <label id="value-error" class="error invalid-feedback animated fadeInDown" for="value"></label>
                    </div>';
    }

    return ["html" => $html, 'categories' => $categories];
}

function getproducts($cat_id){
    $products1 = Product::whereRaw('FIND_IN_SET('.$cat_id.', primary_category_id)')->where('estatus',1)->get(['id','product_title'])->toArray();
   // $products2 = Product::where('child_category_id',$cat_id)->where('subchild_category_id',null)->where('estatus',1)->get();
    // $variants_arr = array();
    // foreach ($products1 as $product1){
    //     $product_variants = ProductVariant::where('product_id',$product1->id)->where('estatus',1)->orderBy('created_at','DESC')->get(['id','product_title'])->toArray();
    //     foreach ($product_variants as $product_variant){
    //         array_push($variants_arr,$product_variant);
    //     }
    // }

    // foreach ($products2 as $product2){
    //     $product_variants = ProductVariant::where('product_id',$product2->id)->where('estatus',1)->orderBy('created_at','DESC')->get(['id','product_title'])->toArray();
    //     foreach ($product_variants as $product_variant){
    //         array_push($variants_arr,$product_variant);
    //     }
    // }

    return $products1;
}

function compressImage($source, $destination, $quality) { 
    // Get image info 
    $imgInfo = getimagesize($source); 
    $mime = $imgInfo['mime']; 
     
    // Create a new image from file 
    switch($mime){ 
        case 'image/jpeg': 
            $image = imagecreatefromjpeg($source); 
            break; 
        case 'image/png': 
            $image = imagecreatefrompng($source); 
            break; 
        case 'image/gif': 
            $image = imagecreatefromgif($source); 
            break; 
        default: 
            $image = imagecreatefromjpeg($source); 
    } 
     
    // Save image 
    imagejpeg($image, $destination, $quality); 
     
    // Return compressed image 
    return $destination; 
}



function is_login(){

    if(session()->has('customer')){
        return true;
    }

    return false;
}



function getPaymentStatus($payment_status){
    if($payment_status == 1){
        $payment_status = "Pending";
        $class = "text-primary";
    }
    elseif($payment_status == 2){
        $payment_status = "Success";
        $class = "text-success";
    }
    elseif($payment_status == 3){
        $payment_status = "Refunded";
        $class = "text-info";
    }
    elseif($payment_status == 4){
        $payment_status = "Cancelled";
        $class = "text-warning";
    }
    elseif($payment_status == 5){
        $payment_status = "Refund Request";
        $class = "text-muted";
    }
    elseif($payment_status == 6){
        $payment_status = "Pay Refund";
        $class = "text-dark";
    }
    elseif($payment_status == 7){
        $payment_status = "Failed";
        $class = "text-danger";
    }

    return ['payment_status' => $payment_status, 'class' => $class];
}

function getPaymentStatusUser($payment_status){
    if($payment_status == 1){
        $payment_status = "Pending";
        $class = "text-primary";
    }
    elseif($payment_status == 2){
        $payment_status = "Success";
        $class = "text-success";
    }
    elseif($payment_status == 3){
        $payment_status = "Refunded";
        $class = "text-info";
    }
    elseif($payment_status == 4){
        $payment_status = "Cancelled";
        $class = "text-warning";
    }
    elseif($payment_status == 5){
        $payment_status = "Refund Request";
        $class = "text-muted";
    }
    elseif($payment_status == 6){
        $payment_status = "Refund Processing";
        $class = "text-dark";
    }
    elseif($payment_status == 7){
        $payment_status = "Failed";
        $class = "text-danger";
    }

    return ['payment_status' => $payment_status, 'class' => $class];
}

function getPaymentType($payment_type)
{
    if ($payment_type == 1){
        $payment_type = "Prepaid";
    }
    elseif ($payment_type == 2){
        $payment_type = "COD";
    }

    return $payment_type;
}

function getOrderStatus($order_status){
    if($order_status == 1){
        $order_status = "New Order";
        $class = "label label-warning";
    }
    elseif($order_status == 2){
        $order_status = "Shipped";
        $class = "label label-info";
    }
    elseif($order_status == 3){
        $order_status = "Delivered";
        $class = "label label-success";
    }
    elseif($order_status == 4){
        $order_status = "Return Request";
        $class = "label label-warning";
    }
    elseif($order_status == 5){
        $order_status = "Return In Transit";
        $class = "label label-secondary";
    }
    elseif($order_status == 6){
        $order_status = "Returned";
        $class = "label label-light";
    }
    elseif($order_status == 7){
        $order_status = "Cancelled";
        $class = "label label-danger";
    }
    elseif($order_status == 8){
        $order_status = "Cancelled";
        $class = "label label-danger";
    }

    return ['order_status' => $order_status, 'class' => $class];
}

function count_order_items($OrderId){
    $order_items = \App\Models\OrderItem::where('order_id',$OrderId)->whereNotIn('order_status',[6,7,8])->count();
    return $order_items;
}

function send_sms($mobile_no, $otp){
    $url = 'https://www.smsgatewayhub.com/api/mt/SendSMS?APIKey=H26o0GZiiEaUyyy0kvOV5g&senderid=MADMRT&channel=2&DCS=0&flashsms=0&number=91'.$mobile_no.'&text=Welcome%20to%20Madness%20Mart,%20Your%20One%20time%20verification%20code%20is%20'.$otp.'.%20Regards%20-%20MADNESS%20MART&route=31&EntityId=1301164983812180724&dlttemplateid=1307165088121527950';
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
    ));
    $response = curl_exec($curl);
    curl_close($curl);
//    echo $response;
}


function sendPushNotification_event($data)
{
    // Do not send push notification from localhost
    if (env('APP_ENV') == 'local') {
        \Illuminate\Support\Facades\Log::info($data);
        \Illuminate\Support\Facades\Log::info("local environment");
        return true;
    }else{
        $tokens_android = \App\Models\CustomerDeviceToken::where('device_type','android')->pluck('token')->all();
        $tokens_ios = \App\Models\CustomerDeviceToken::where('device_type','ios')->pluck('token')->all();
      
        if (count($tokens_android) == 0 && count($tokens_ios) == 0) {
//                Log::info('no token found');
            return false;
        }

        if (isset($tokens_ios) && !empty($tokens_ios)){
            $ios_fields = array(
                'registration_ids' => $tokens_ios,
                'data' => $data,
                'notification' => array(
                    "title" => $data['title'],
                    "body" => $data['message'],
                    "image" => url($data['image']),
                    "priority" => "high",
                    "sound" => "default",
                )
            );
            sendNotification($ios_fields,"ios");
        }

        if (isset($tokens_android) && !empty($tokens_android)){
            $android_fields = array(
                'registration_ids' => $tokens_android,
                'data' => $data,
                'notification' => array(
                    "title" => $data['title'],
                    "body" => $data['message'],
                    "image" => url($data['image']),
                    "priority" => "high",
                    "sound" => "default",
                )
            );
            sendNotification($android_fields,"android");
        }


        /*$data  = array(
            'registration_ids' => $customers,
            'data' => $data,
        );
        sendNotification($data,"android");*/
        return true;
    }
}

function sendPushNotification_remainder($bookinguserid,$data)
{
    // Do not send push notification from localhost
    // if (env('APP_ENV') == 'local') {
    //     \Illuminate\Support\Facades\Log::info($data);
    //     \Illuminate\Support\Facades\Log::info("local environment");
    //     return true;
    // }else{
        $tokens_android = \App\Models\CustomerDeviceToken::where('user_id',$bookinguserid)->where('device_type','android')->pluck('token')->all();
        $tokens_ios = \App\Models\CustomerDeviceToken::where('user_id',$bookinguserid)->where('device_type','ios')->pluck('token')->all();
      
        if (count($tokens_android) == 0 && count($tokens_ios) == 0) {
//                Log::info('no token found');
            return false;
        }

        if (isset($tokens_ios) && !empty($tokens_ios)){
            $ios_fields = array(
                'registration_ids' => $tokens_ios,
                'data' => $data,
                'notification' => array(
                    "title" => $data['title'],
                    "body" => $data['message'],
                    "image" => url($data['image']),
                    "priority" => "high",
                    "sound" => "default",
                )
            );
            sendNotification($ios_fields,"ios");
        }

        if (isset($tokens_android) && !empty($tokens_android)){
            $android_fields = array(
                'registration_ids' => $tokens_android,
                'data' => $data,
                'notification' => array(
                    "title" => $data['title'],
                    "body" => $data['message'],
                    "image" => url($data['image']),
                    "priority" => "high",
                    "sound" => "default",
                )
            );
            sendNotification($android_fields,"android");
        }


        /*$data  = array(
            'registration_ids' => $customers,
            'data' => $data,
        );
        sendNotification($data,"android");*/
        return true;
   // }
}

function sendNotification($data,$type){
    $api_key = env('ANDROID_NOTIFICATION_KEY');
    if($type == "ios"){
        $api_key = env('IOS_NOTIFICATION_KEY');
    }
    $headers = array('Authorization: key=' . $api_key, 'Content-Type: application/json');
    $url = 'https://fcm.googleapis.com/fcm/send';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    $result = curl_exec($ch);
    curl_close($ch);

    $data = explode(':', $result);
    $sucess = explode(",", $data[2]);

    return true;
}


