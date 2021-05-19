<?php

namespace ppe\dvtinh\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use ppe\dvtinh\Database\Factories\UserAvatarFactory;

class UserAvatar extends Model
{
    protected $connection = "ppe_core";
    use HasFactory;
    protected $table = "user_avatars";
    public $timestamps = true;

    protected static function newFactory()
    {
        return UserAvatarFactory::new();
    }

    protected $fillable = [
        'user_id',
        'background_file',
        'face',
        'hair',
        'eyebrows',
        'body',
        'eyes',
        'mouth',
        'nose',
        'shirt',
        'trouser',
        'shoes',
    ];

    protected $casts = [
        'face' => 'array',
        'hair' => 'array',
        'eyebrows' => 'array',
        'body' => 'array',
        'eyes' => 'array',
        'mouth' => 'array',
        'nose' => 'array',
        'shirt' => 'array',
        'trouser' => 'array',
        'shoes' => 'array',
    ];
}
