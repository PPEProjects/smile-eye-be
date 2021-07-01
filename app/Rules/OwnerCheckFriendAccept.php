<?php

namespace App\Rules;

use App\Models\Friend;
use App\Models\User;
use Illuminate\Contracts\Validation\Rule;

class OwnerCheckFriendAccept implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        //
        return Friend::where('user_id', \auth()->id())
            ->where('user_id_friend',$value )
            ->where('status','accept')
            ->orWhere('user_id_friend', \auth()->id())
            ->where('user_id',$value )
            ->where('status','accept')
            ->exists();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Permission denied.';
    }
}
