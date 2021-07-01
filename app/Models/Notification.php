<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use \Staudenmeir\EloquentJsonRelations\HasJsonRelationships;
    use HasFactory;

    protected $connection = "mysql";
    protected $table = 'notifications';

    protected $fillable = [
        'id',
        'user_id',
        'user_receive_id',
        'type',
        'type_id',
        'content',
        'is_read',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'content' => 'json',

    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
    public function user_receive(){
        return $this->belongsTo(User::class,'user_receive_id');
    }
}
