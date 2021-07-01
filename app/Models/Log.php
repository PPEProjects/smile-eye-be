<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Log extends Model
{
    use HasFactory;
    protected $connection = "mysql";
    protected $table = "logs";

    protected $fillable = [
        'id',
        'user_id',
        'table',
        'action',
        'content',

    ];
    protected $casts = [
        'content' => 'array'
    ];
    public function user(){
        return $this->belongsTo(User::class);
    }

}
