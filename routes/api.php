<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\PassportAuthController;

use App\Http\Controllers\API\EventController;
use App\Http\Controllers\API\EvenBookingController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('login', [\App\Http\Controllers\API\AuthController::class, 'login']);
Route::post('verify_otp',[\App\Http\Controllers\API\UserController::class,'verify_otp']);
Route::post('send_otp',[\App\Http\Controllers\API\UserController::class,'send_otp']);


Route::group(['middleware' => 'auth:api'], function(){
    

    
});
Route::post('edit_profile',[\App\Http\Controllers\API\UserController::class,'edit_profile']);
Route::post('view_profile',[\App\Http\Controllers\API\UserController::class,'view_profile']);
Route::post('add_edit_member',[\App\Http\Controllers\API\UserController::class,'add_edit_member']);
Route::get('getMembers/{id}',[\App\Http\Controllers\API\UserController::class,'getMembers']);
Route::get('removeMember/{id}',[\App\Http\Controllers\API\UserController::class,'removeMember']);
Route::get('getZone',[\App\Http\Controllers\API\UserController::class,'getZone']);

Route::post('add_edit_member_family',[\App\Http\Controllers\API\UserController::class,'add_edit_member_family']);
Route::get('getMemberFamily/{id}',[\App\Http\Controllers\API\UserController::class,'getMemberFamily']);
Route::get('removeMemberFamily/{id}',[\App\Http\Controllers\API\UserController::class,'removeMemberFamily']);

Route::post('update_token',[\App\Http\Controllers\API\UserController::class,'update_token']);

Route::get('getHome', [EventController::class,'getHome']);

Route::get('getEvents', [EventController::class,'getEvents']);
Route::post('viewEvent',[EventController::class,'viewEvent']);


Route::post('eventBooking',[EvenBookingController::class,'eventBooking']);
