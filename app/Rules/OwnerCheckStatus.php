<?php

namespace App\Rules;

use App\Models\Todolist;
use Illuminate\Contracts\Validation\Rule;

class OwnerCheckStatus implements Rule
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
        $status_code = array("todo","done");
        if(in_array($value, $status_code)){
            return  "oke";
        }


    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Status in (todo or done)';
    }
}
