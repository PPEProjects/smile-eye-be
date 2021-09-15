<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JapaneseGoal extends Model
{
    use \Staudenmeir\EloquentJsonRelations\HasJsonRelationships;
    use HasFactory;
    use SoftDeletes;
    protected $connection = "mysql";
    protected $table = "japanese_goals";

    protected $fillable = [
        'id',
        'goal_id',
        'user_id',
        'type',
        'more',
        'total_score',
        'each_score',
        'created_at',
        'updated_at'
    ];
    protected $casts = [
        'more' => 'json',
    ];
    public function goal(){
        return $this->belongsTo(goal::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, "user_id");
    }
}
