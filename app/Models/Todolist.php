<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Todolist extends Model
{
    use \Staudenmeir\EloquentJsonRelations\HasJsonRelationships;
    use HasFactory;
    use SoftDeletes;
    protected $connection = "mysql";
    protected $table    = "todolists";

    protected $fillable = [
        'id',
        'goal_id',
        'task_id',
        'name',
        'status',
        'checked_at',
        'user_id'
    ];

    public function goal()
    {
        return $this->belongsTo(Goal::class, "goal_id");
    }
    public function task()
    {
        return $this->belongsTo(Task::class, "task_id");
    }
    public function user()
    {
        return $this->belongsTo(User::class, "user_id");
    }
    public function notes(){
        return $this->hasMany(Note::class,"todolist_id");
    }
    public function attachments(){
        return $this->belongsToJson(Attachment::class,'attachment_ids','id');
    }


}
