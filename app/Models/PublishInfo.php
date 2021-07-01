<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PublishInfo extends Model
{
    use \Staudenmeir\EloquentJsonRelations\HasJsonRelationships;
    use HasFactory;

    protected $connection = "mysql";
    protected $table = 'publish_infos';

    protected $fillable = [
        'id',
        'general_id',
        'user_invite_id',
        'status',
        'rule',
        'is_copy',
    ];

    public function general(){
        return $this->belongsTo(GeneralInfo::class,'general_id','id' );
    }

    public function friends(){
        return $this->belongsTo(Friend::class);
    }
    public function user_invite(){
        return $this->belongsTo(User::class,'user_invite_id','id');
    }

}
