<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CoachMember extends Model
{
    use \Staudenmeir\EloquentJsonRelations\HasJsonRelationships;
    use HasFactory;
    use SoftDeletes;

    protected $connection = "mysql";
    protected $table = 'coach_members';

    protected $fillable = [
        'id',
        'user_id',
        'scale',
        'org',
        'business_field',
        'goal_ids',
        'teacher_ids'
    ];
    protected $casts = [
        'goal_ids' => 'json',
        'teacher_ids' => 'json',
    ];
    public function user(){
        return $this->belongsTo(User::class);
    }
}
