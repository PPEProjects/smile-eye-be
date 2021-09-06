<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class JapaneseKanji extends Model
{
    use \Staudenmeir\EloquentJsonRelations\HasJsonRelationships;
    use HasFactory;
    use SoftDeletes;
    protected $connection = "mysql";
    protected $table = "japanese_kanjis";

    protected $fillable = [
        'id',
        'user_id',
        'name',
        'more',
        'created_at',
        'updated_at'
    ];
    protected $casts = [
        'more' => 'json',
    ];
    public function user()
    {
        return $this->belongsTo(User::class, "user_id");
    }
}
