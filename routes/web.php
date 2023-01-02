<?php

use Illuminate\Support\Facades\Route;



/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/



//Admin  Rpute
Route::get('admin',[\App\Http\Controllers\admin\AuthController::class,'index'])->name('admin.login');
Route::post('adminpostlogin', [\App\Http\Controllers\admin\AuthController::class, 'postLogin'])->name('admin.postlogin');
Route::get('logout', [\App\Http\Controllers\admin\AuthController::class, 'logout'])->name('admin.logout');
Route::get('admin/403_page',[\App\Http\Controllers\admin\AuthController::class,'invalid_page'])->name('admin.403_page');

Route::get('admin/403_page',[\App\Http\Controllers\admin\AuthController::class,'invalid_page'])->name('admin.403_page');

//Route::group(['prefix'=>'admin','as'=>'admin.'],function () {
Route::group(['prefix'=>'admin','middleware'=>['auth'],'as'=>'admin.'],function () {
    Route::get('dashboard', [\App\Http\Controllers\admin\DashboardController::class, 'index'])->name('dashboard');
    Route::get('events',[\App\Http\Controllers\admin\EventController::class,'index'])->name('events.list');
    Route::post('alleventslist',[\App\Http\Controllers\admin\EventController::class,'allEventlists'])->name('alleventslist');
    Route::post('addorupdateevent',[\App\Http\Controllers\admin\EventController::class,'addorupdateevent'])->name('events.addorupdate');
    Route::post('addorupdateeventfree',[\App\Http\Controllers\admin\EventController::class,'addorupdateeventfree'])->name('events.addorupdatefree');
    Route::get('events/{id}/edit',[\App\Http\Controllers\admin\EventController::class,'editevent'])->name('event.edit');
    Route::get('events/{id}/delete',[\App\Http\Controllers\admin\EventController::class,'deleteevent'])->name('events.delete');
    Route::get('eventfree/{id}/delete',[\App\Http\Controllers\admin\EventController::class,'deleteeventfree'])->name('eventfree.delete');
    Route::get('events/{id}/sendnotificationevent',[\App\Http\Controllers\admin\EventController::class,'sendnotificationevent'])->name('event.sendnotificationevent');
    Route::get('events/{id}/sendnotificationbooking',[\App\Http\Controllers\admin\EventController::class,'sendnotificationbooking'])->name('event.sendnotificationbooking');


    Route::get('users',[\App\Http\Controllers\admin\UserController::class,'index'])->name('users.list');
    Route::post('addorupdateuser',[\App\Http\Controllers\admin\UserController::class,'addorupdateuser'])->name('users.addorupdate');
    Route::post('alluserslist',[\App\Http\Controllers\admin\UserController::class,'alluserslist'])->name('alluserslist');
    Route::get('changeuserstatus/{id}',[\App\Http\Controllers\admin\UserController::class,'changeuserstatus'])->name('users.changeuserstatus');
    Route::get('users/{id}/edit',[\App\Http\Controllers\admin\UserController::class,'edituser'])->name('users.edit');
    Route::get('users/{id}/delete',[\App\Http\Controllers\admin\UserController::class,'deleteuser'])->name('users.delete');
    Route::get('memberusers/{id}/child',[\App\Http\Controllers\admin\UserController::class,'memberusers'])->name('users.memberusers');
    Route::post('allmemberuserslist/{id}',[\App\Http\Controllers\admin\UserController::class,'allmemberuserslist'])->name('allmemberuserslist');
    Route::get('familymemberusers/{id}/family',[\App\Http\Controllers\admin\UserController::class,'familymemberusers'])->name('users.familymemberusers');
    Route::post('allfamilymemberuserslist/{id}',[\App\Http\Controllers\admin\UserController::class,'allfamilymemberuserslist'])->name('allfamilymemberuserslist');

    Route::post('addorupdatescanneruser',[\App\Http\Controllers\admin\EventController::class,'addorupdatescanneruser'])->name('events.addorupdatescanneruser');
    Route::get('scanneruser/{id}/edit',[\App\Http\Controllers\admin\EventController::class,'editscanneruser'])->name('scanneruser.edit');

    Route::get('banners',[\App\Http\Controllers\admin\BannerController::class,'index'])->name('banners.list');
    Route::get('banners/create',[\App\Http\Controllers\admin\BannerController::class,'create'])->name('banners.add');
    Route::post('banners/save',[\App\Http\Controllers\admin\BannerController::class,'save'])->name('banners.save');
    Route::post('allbannerlist',[\App\Http\Controllers\admin\BannerController::class,'allbannerlist'])->name('allbannerlist');
    Route::get('changebannerstatus/{id}',[\App\Http\Controllers\admin\BannerController::class,'changebannerstatus'])->name('banners.changeblogstatus');
    Route::get('banners/{id}/delete',[\App\Http\Controllers\admin\BannerController::class,'deletebanner'])->name('banners.delete');
    Route::get('banners/{id}/edit',[\App\Http\Controllers\admin\BannerController::class,'editbanner'])->name('banners.edit');
    Route::post('banners/uploadfile',[\App\Http\Controllers\admin\BannerController::class,'uploadfile'])->name('banners.uploadfile');
    Route::post('banners/removefile',[\App\Http\Controllers\admin\BannerController::class,'removefile'])->name('banners.removefile');

    Route::get('bookings/{id}',[\App\Http\Controllers\admin\BookingController::class,'index'])->name('userbookings.list');
    Route::post('allbookinglist',[\App\Http\Controllers\admin\BookingController::class,'allbookinglist'])->name('allbookinglist');

    Route::get('contacts',[\App\Http\Controllers\admin\ContactusController::class,'index'])->name('contacts.list');
    Route::post('allcontactslist',[\App\Http\Controllers\admin\ContactusController::class,'allcontactslist'])->name('allcontactslist');

    Route::get('settings',[\App\Http\Controllers\admin\SettingsController::class,'index'])->name('settings.list');
    Route::post('updateInvoiceSetting',[\App\Http\Controllers\admin\SettingsController::class,'updateInvoiceSetting'])->name('settings.updateInvoiceSetting');
    Route::get('settings/edit',[\App\Http\Controllers\admin\SettingsController::class,'editSettings'])->name('settings.edit');

    Route::get('request_karya_karta',[\App\Http\Controllers\admin\RequestKaryaKartaController::class,'index'])->name('request_karya_karta.list');
    Route::post('allRequestKaryaKartalist',[\App\Http\Controllers\admin\RequestKaryaKartaController::class,'allRequestKaryaKartalist'])->name('allRequestKaryaKartalist');
    Route::get('request_karya_karta/create/{id}',[\App\Http\Controllers\admin\RequestKaryaKartaController::class,'create'])->name('request_karya_karta.add');
    Route::post('request_karya_karta/save',[\App\Http\Controllers\admin\RequestKaryaKartaController::class,'save'])->name('request_karya_karta.save');
    Route::get('karyakartarejectstatus/{id}',[\App\Http\Controllers\admin\RequestKaryaKartaController::class,'rejectstatus'])->name('review.karyakartarejectstatus');
    Route::get('karyakartaacceptstatus/{id}',[\App\Http\Controllers\admin\RequestKaryaKartaController::class,'acceptstatus'])->name('review.karyakartaacceptstatus');

    Route::post('categories/uploadfile',[\App\Http\Controllers\admin\CategoryController::class,'uploadfile'])->name('categories.uploadfile');
    Route::post('categories/removefile',[\App\Http\Controllers\admin\CategoryController::class,'removefile'])->name('categories.removefile');
    
    
});



Route::group(['middleware'=>['auth']],function (){
    Route::get('profile',[\App\Http\Controllers\admin\ProfileController::class,'profile'])->name('profile');
    Route::get('profile/{id}/edit',[\App\Http\Controllers\admin\ProfileController::class,'edit'])->name('profile.edit');
    Route::post('profile/update',[\App\Http\Controllers\admin\ProfileController::class,'update'])->name('profile.update');
    
});






