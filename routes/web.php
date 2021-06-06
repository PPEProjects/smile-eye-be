<?php
use Illuminate\Support\Facades\Route;
use ppeCore\dvtinh\Http\Controllers\AuthController;
use ppeCore\dvtinh\Http\Controllers\VerificationController;
use ppeCore\dvtinh\Http\Controllers\ResetPasswordController;

Route::get("/ppe", function (){
    dd("ok");
});
Route::group(['prefix' => '/ppe-core/auth'], function() {
    Route::post("/register", [AuthController::class, 'register']);
    Route::post("/login", [AuthController::class, 'login']);
    Route::get('/logout', [AuthController::class, 'logout']);
    Route::post('/change-pass', [AuthController::class, 'changePass']);
//    Route::get('/change-password', [AuthController::class,'changePass']);
    Route::get('/generate-url',[AuthController::class,'generateUrl']);
    Route::get('/handle',[AuthController::class,'authHandle']);
    Route::post('/reset-password', [ResetPasswordController::class,'sendMail']);
    Route::post('/reset', [ResetPasswordController::class,'reset']);

});
//Auth::routes(['verify' => true]);
Route::get('email/verify/{id}', [VerificationController::class,'verify']);

//Route::get('email/resend', 'VerificationController@resend')->name('verification.resend');



