<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JapanesePost extends Model
{
    use HasFactory;
    protected $connection = "mysql";
    protected $table = "japanese_posts";

    protected $fillable = [
        'id',
        'user_id',
        'goal_id',      
         'title',
         'description',
         'media',
         'likes',
         'more',
        'created_at',
        'updated_at'
    ];
    protected $casts = [
        'media' => 'json',
        'likes' => 'json',
        'more' => 'json',
    ];
    public function goal(){
        return $this->belongsTo(goal::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class, "user_id");
    }
}
