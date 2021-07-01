<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAvatar extends \ppeCore\dvtinh\Models\UserAvatar
{

    public function user()
    {
//        return $this->belongsTo('App\Models\Users', 'id', 'user_id');
        return $this->belongsTo(User::class);
    }
    // HI
}
