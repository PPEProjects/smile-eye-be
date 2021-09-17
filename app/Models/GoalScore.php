<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class GoalScore extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $connection = "mysql";
    protected $table = "goal_scores";

    protected $fillable = [
        'id',
        'user_id',
        'goal_id',      
        'scores',
        'created_at',
        'updated_at'
    ];
    protected $casts = [
        'scores' => 'json',
    ];
    public function goal(){
        return $this->belongsTo(goal::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class, "user_id");
    }
}