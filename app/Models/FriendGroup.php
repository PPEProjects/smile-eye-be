<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FriendGroup extends Model
{
    use \Staudenmeir\EloquentJsonRelations\HasJsonRelationships;
    use HasFactory;
    protected $connection = "mysql";
    protected $table = "friend_groups";
    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'name',
        'people',
    ];

    protected $casts = [
        'people' => 'json',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, "user_id");
    } 
}