<?php
use Illuminate\Support\Facades\Route;
use ppeCore\dvtinh\Http\Controllers\AuthController;

Route::get("/ppe", function (){
    dd("ok");
});
Route::group(['prefix' => '/ppe-core/auth'], function() {
    Route::post("/register", [AuthController::class, 'register']);
    Route::post("/login", [AuthController::class, 'login']);
    Route::get("/logout", [AuthController::class, 'logout']);
    Route::get('/generate-url',[AuthController::class,'generateUrl']);
    Route::get('/handle',[AuthController::class,'authHandle']);
});
Auth::routes(['verify' => true]);
Route::get('profile', function () {
    // Only verified users may enter...
})->middleware('verified');



