<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GoalMember extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $connection = "mysql";
    protected $table = "goal_members";

    protected $fillable = [
        'id',
        'user_id',
        'add_user_id',
        'goal_id',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
    public function goal(){
        return $this->belongsTo(goal::class);
    }
}
