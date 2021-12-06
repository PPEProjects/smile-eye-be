<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $connection = "mysql";
    protected $table = "payments";

    protected $fillable = [
        'id',
        'user_id',
        'add_user_id',
        'goal_id',
        'type',
        'status',
        'money',
        'attachments'
    ];
    protected $casts = [
        'attachments' => 'json',
    ];
    public function user(){
        return $this->belongsTo(User::class);
    }

    public function add_user()
    {
        return $this->belongsTo(User::class, 'add_user_id', 'id');
    }

    public function goal(){
        return $this->belongsTo(goal::class);
    }
}
