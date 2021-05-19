<?php
//
//Route::get("/ppe",function (){
//    return \App\Models\User::all();;
//});
//Route::post("/register", [\ppe\dvtinh\Http\Controllers\AuthController::class, 'register']);

Route::group(['prefix' => 'ppe-core/auth'], function() {
//    Route::post("/register", function (){
//        dd(31);
//    });
    Route::post("/register", [\ppeCore\dvtinh\Http\Controllers\AuthController::class, 'register']);
//    Route::post("/login", [\ppeCore\dvtinh\Http\Controllers\AuthController::class, 'login']);
});
