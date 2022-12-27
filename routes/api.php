<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\PassportAuthController;

use App\Http\Controllers\API\EventController;
use App\Http\Controllers\API\EvenBookingController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\AuthController;

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

Route::post('login', [AuthController::class, 'login']);
Route::post('verify_otp',[UserController::class,'verify_otp']);
Route::post('send_otp',[UserController::class,'send_otp']);


Route::group(['middleware' => 'auth:api'], function(){
    

    
});
Route::post('edit_profile',[UserController::class,'edit_profile']);
Route::post('view_profile',[UserController::class,'view_profile']);
Route::post('add_edit_member',[UserController::class,'add_edit_member']);
Route::get('getMembers/{id}',[UserController::class,'getMembers']);
Route::get('removeMember/{id}',[UserController::class,'removeMember']);
Route::get('getZone',[UserController::class,'getZone']);

Route::post('add_edit_member_family',[UserController::class,'add_edit_member_family']);
Route::get('getMemberFamily/{id}',[UserController::class,'getMemberFamily']);
Route::get('removeMemberFamily/{id}',[UserController::class,'removeMemberFamily']);

Route::post('update_token',[UserController::class,'update_token']);

Route::get('getHome', [EventController::class,'getHome']);

Route::get('getEvents/{id}', [EventController::class,'getEvents']);
Route::post('viewEvent',[EventController::class,'viewEvent']);
Route::post('viewSummary',[EventController::class,'viewSummary']);


Route::post('eventBooking',[EvenBookingController::class,'eventBooking']);
Route::post('eventScanner',[EvenBookingController::class,'eventScanner']);

Route::get('settings',[UserController::class,'settings']);

Route::post('add_edit_professional',[UserController::class,'add_edit_professional']);
Route::get('getProfessionalDetails/{id}',[UserController::class,'getProfessionalDetails']);

Route::post('contactus',[UserController::class,'contact']);


