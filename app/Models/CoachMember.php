<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoachMember extends Model
{
    use \Staudenmeir\EloquentJsonRelations\HasJsonRelationships;
    use HasFactory;

    protected $connection = "mysql";
    protected $table = 'coach_members';

    protected $fillable = [
        'id',
        'user_id',
        'scale',
        'org',
        'business_field',
        'goal_ids',
        'teacher_id'
    ];
    protected $casts = [
        'goal_ids' => 'json',
    ];
    public function user(){
        return $this->belongsTo(User::class);
    }
}
