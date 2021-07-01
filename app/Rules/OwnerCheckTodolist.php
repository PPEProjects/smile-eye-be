<?php

namespace App\Rules;

use App\Models\Todolist;
use Illuminate\Contracts\Validation\Rule;

class OwnerCheckTodolist implements Rule
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
        return Todolist::where('id', $value)
            ->OrWhere('goal_id', $value)
            ->OrWhereNull('goal_id')
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
