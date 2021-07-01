<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContestInfo extends Model
{
    use \Staudenmeir\EloquentJsonRelations\HasJsonRelationships;
    use HasFactory;

    protected $connection = "mysql";
    protected $table = 'contest_infos';

    protected $fillable = [
        'id',
        'general_id',
        'user_invite_id',
        'status',
    ];

    public function general_infos(){
        return $this->belongsTo(GeneralInfo::class );
    }

    public function friends(){
        return $this->belongsTo(Friend::class);
    }

}
