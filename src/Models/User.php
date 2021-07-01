<?php

namespace ppeCore\dvtinh\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use ppeCore\dvtinh\Database\Factories\UserFactory;

class User extends Authenticatable
{
    use \Staudenmeir\EloquentJsonRelations\HasJsonRelationships;
    protected $connection = 'ppe_core';
    use HasFactory, Notifiable;
    use HasApiTokens;

    protected static function newFactory()
    {
        return UserFactory::new();
    }
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'remember_token',
        'avatar',
        'first_name',
        'platform',
        'access_token_social',
        'social_id',
        'username',
        'phone_number',
        'quotes',
        'avatar_attachment_id',
        'background_attachment_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime'
    ];
}
