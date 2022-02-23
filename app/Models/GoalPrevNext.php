<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GoalPrevNext extends Model
{
    use HasFactory;

    protected $connection = "mysql";
    protected $table = "goal_prev_nexts";

    protected $fillable = [
        'id',
        'root_id',
        'jp_ids',
    ];
    protected $casts = [
        'jp_ids' => 'json',
    ];
}
