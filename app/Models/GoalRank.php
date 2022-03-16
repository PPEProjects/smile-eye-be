<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoalRank extends Model
{
    use HasFactory;

    protected $connection = "mysql";
    protected $table = "goal_ranks";
    protected $fillable = [
        'id',
        'user_id',
        'goal_id',
        'pin_index',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function goal()
    {
        return $this->belongsTo(Goal::class);
    }
}
