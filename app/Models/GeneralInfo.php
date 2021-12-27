<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use ppeCore\dvtinh\Services\AttachmentService;

class GeneralInfo extends Model
{
    use \Staudenmeir\EloquentJsonRelations\HasJsonRelationships;
    use HasFactory;
    protected $connection = "mysql";
    protected $table = 'general_infos';

    protected $fillable = [
        'id',
        'user_id',
        'goal_id',
        'task_id',
        'todolist_id',
        'address',
        'zalo',
        'repeat',
        'reminder',
        'action_at',
        'action_at_time',
        'note',
        'attachment_ids',
        'storage',
        'publish',
        'contest',
        'color',
        'created_at',
        'updated_at',
    ];
    protected $casts = [
        'attachment_ids' => 'json',
        'storage' => 'json',
        'contest' => 'json',

    ];

    public function user(){
        return $this->belongsTo(User::class,'user_id','id');
    }
    public function goal(){
        return $this->belongsTo(goal::class);

    }
    public function task(){
        return $this->belongsTo(Task::class);
    }
    public function todolist(){
        return $this->belongsTo(Todolist::class);
    }
    public function attachments(){
        return $this->belongsToJson(Attachment::class,'attachment_ids','id');
    }
    public function publishs(){
        return $this->hasMany(PublishInfo::class,"general_id");
    }
    public function contest(){
        return $this->hasMany(ContestInfo::class);
    }
    public function assign(){
        return $this->hasMany(AssignInfo::class);
    }
    public function comments(){
        return $this->hasMany(Comment::class);
    }
}
