<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Friend extends Model
{
    use \Staudenmeir\EloquentJsonRelations\HasJsonRelationships;
    use HasFactory;
    protected $connection = "mysql";
    protected $table = "friends";
    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'user_id_friend',
        'status',
        'goal_ids',
    ];

    protected $casts = [
        'goal_ids' => 'json',
    ];

    public function user()
    {
//        return $this->belongsTo('App\Models\Users', 'id', 'user_id');
        return $this->belongsTo(User::class);
    }
    // HI
    public function friend()
    {
//        return $this->belongsTo('App\Models\Users', 'id', 'user_id');
        return $this->hasOne(User::class,"id","user_id_friend");
    }
    public function goals()
    {
//        \Illuminate\Support\Facades\Log::channel('single')->info('$friend', [$friend]);
        return $this->belongsToJson(Goal::class, 'goal_ids', 'id');
//        return $this->hasMany(Goal::class,"id","goal_ids");
    }
}
