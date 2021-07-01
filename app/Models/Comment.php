<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Challenge;
use ppeCore\dvtinh\Services\AttachmentService;

class Comment extends Model
{
    use \Staudenmeir\EloquentJsonRelations\HasJsonRelationships;
    use HasFactory;
    protected $connection = "mysql";
    protected $table = 'comments';

    protected $fillable = [
        'id',
        'general_id',
        'parent_id',
        'user_id',
        'mode',
        'content',
        'attachment_ids',
        'created_at',
        'updated_at',
    ];
    protected $casts = [
        'attachment_ids' => 'json',
    ];

    public function parent(){
        return $this->belongsTo(Comment::class,'parent_id','id');
    }
    public function user(){
        return $this->belongsTo(User::class,'user_id','id');

    }
    public function attachments(){
        return $this->belongsToJson(Attachment::class,'attachment_ids','id');
    }

    public function  general(){
        return $this->belongsTo(GeneralInfo::class,'general_id','id');
    }
//    public function children(){
//        return $this->hasMany(Comment::class,'parent_id');
//    }
}
