<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Goal extends Model
{
   use \Staudenmeir\EloquentJsonRelations\HasJsonRelationships;
    use HasFactory;
    use SoftDeletes;
    protected $connection = "mysql";
    protected $table = "goals";

    protected $fillable = [
        'id',
        'parent_id',
        'name',
        'start_day',
        'end_day',
        'progress',
        'status',
        'task_id',
        'is_pined',
        'report_type',
        'user_id'
    ];

    public function todolists()
    {
        return $this->hasMany(Todolist::class, 'goal_id');
    }
    public function task()
    {
        return $this->belongsTo(Todolist::class, 'task_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, "user_id");
    }
    public function challenge()
    {
        return $this->hasMany(Challenge::class, "user_id");
    }
    public function parent()
    {
        return $this->belongsTo(Goal::class, "parent_id");
    }
    public function friend(){
        return $this->hasManyJson(Friend::class,"id","goal_ids");
    }
    public function attachment(){
        return $this->belongsToJson(Attachment::class,"attachment_ids");
    }
}
