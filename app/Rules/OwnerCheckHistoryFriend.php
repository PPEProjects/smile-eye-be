<?php

namespace App\Rules;

use App\Models\HistoryFriend;
use Illuminate\Contracts\Validation\Rule;

class OwnerCheckHistoryFriend implements Rule
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
        return HistoryFriend::where('user_id', \auth()->id())
            ->where('id', $value)
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
