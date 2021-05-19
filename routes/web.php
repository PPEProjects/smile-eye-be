<?php

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


