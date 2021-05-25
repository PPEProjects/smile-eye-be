<?php

namespace ppeCore\dvtinh\Models;
 use Illuminate\Database\Eloquent\Model;

 class PasswordReset extends Model{

     protected $connection = "ppe_core";
     protected $table = "password_resets";
     public $timestamps = true;

     protected $fillable = [
         'email',
         'token',
     ];
 }