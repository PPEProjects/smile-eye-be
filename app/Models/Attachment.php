<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Challenge;

class Attachment extends Model
{
    use \Staudenmeir\EloquentJsonRelations\HasJsonRelationships;
    use HasFactory;
    protected $connection = "mysql";
    protected $table = 'attachments';

    protected $fillable = [
        'id',
        'user_id',
        'file',
        'file_name',
        'file_type',
        'file_size'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }


}
