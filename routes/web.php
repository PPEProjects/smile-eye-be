<?php
//
//Route::get("/ppe",function (){
//    return \App\Models\User::all();;
//});
//Route::post("/register", [\ppe\dvtinh\Http\Controllers\AuthController::class, 'register']);

Route::group(['prefix' => 'ppe-core'], function() {
    Route::post("/register", [\ppe\dvtinh\Http\Controllers\AuthController::class, 'register']);
});
