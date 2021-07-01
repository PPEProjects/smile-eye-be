<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssignInfo extends Model
{
    use \Staudenmeir\EloquentJsonRelations\HasJsonRelationships;
    use HasFactory;

    protected $connection = "mysql";
    protected $table = 'assign_infos';

    protected $fillable = [
        'id',
        'general_id',
        'user_invite_id',
        'status',
    ];

    public function general(){
        return $this->belongsTo(GeneralInfo::class );
    }

    public function friend(){
        return $this->belongsTo(Friend::class);
    }

}
