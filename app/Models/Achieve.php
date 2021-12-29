<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Achieve extends Model
{
    use \Staudenmeir\EloquentJsonRelations\HasJsonRelationships;
    use HasFactory;

    protected $connection = "mysql";
    protected $table = 'achieves';

    protected $fillable = [
        'id',
        'user_id',
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
    public function user(){
        return $this->belongsTo(User::class);
    }
    public function user_invite(){
        return $this->belongsTo(User::class);
    }
    public function goal(){
         return $this->belongsTo(Goal::class);
    }
}
