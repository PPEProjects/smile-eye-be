<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Note extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $connection = "mysql";
    protected $table = "notes";

    protected $fillable = [
        'id',
        'user_id',
        'checked_at',
        'content',
        'created_at',
        'update_at'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
}
