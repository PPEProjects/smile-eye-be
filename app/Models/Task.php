<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use \Staudenmeir\EloquentJsonRelations\HasJsonRelationships;
    use HasFactory;
    use SoftDeletes;
    protected $connection = "mysql";
    protected $table = "tasks";

    protected $fillable = [
        'id',
        'name',
        'goal_id',
        'user_id'
    ];


    public function todolists()
    {
        return $this->hasMany(Todolist::class, 'task_id');
    }
    public function goal()
    {
        return $this->belongsTo(Goal::class, 'task_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class);
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
    public function attachments(){
        return $this->hasOne(Attachment::class,"goal_id","id");
    }
    public function general(){
        return $this->hasMany(GeneralInfo::class);
    }
}
