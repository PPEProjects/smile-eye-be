<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JapaneseGoal extends Model
{
    use \Staudenmeir\EloquentJsonRelations\HasJsonRelationships;
    use HasFactory;
    use SoftDeletes;
    protected $connection = "mysql";
    protected $table = "japanese_goals";

    protected $fillable = [
        'id',
        'goal_id',
        'type',
        'more',
        'attachments_1',
        'attachments_2',
        'attachments_3',
        'score',
        'created_at',
        'updated_at'
    ];
    protected $casts = [
        'more' => 'json',
        'attachments_1' => 'json',
        'attachments_2' => 'json',
        'attachments_3' => 'json',
    ];
    public function goal(){
        return $this->belongsTo(goal::class);
    }

}
