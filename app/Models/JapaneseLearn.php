<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JapaneseLearn extends Model
{
    use HasFactory;
    protected $connection = "mysql";
    protected $table = "japanese_learn";

    protected $fillable = [
        'id',
        'user_id',
        'goal_id',      
        'created_at',
        'updated_at'
    ];
    public function goal(){
        return $this->belongsTo(goal::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class, "user_id");
    }
}
