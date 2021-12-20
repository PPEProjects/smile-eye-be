<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoalTemplate extends Model
{
    use \Staudenmeir\EloquentJsonRelations\HasJsonRelationships;
    use HasFactory;

    protected $connection = "mysql";
    protected $table = 'goal_templates';

    protected $fillable = [
        'id',
        'user_id',
        'goal_id',
        'status',
        'request',
        'checked_time'
    ];
    public function goal(){
        return $this->belongsTo(goal::class);
    }
    public function user(){
        return $this->belongsTo(User::class);
    }
}
