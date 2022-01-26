<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class isValidEmailOrPhone implements Rule
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
        return ($this->validPhone($value) || $this->validEmail($value));
    }

    function validPhone($value)
    {
        if(strlen($value) === 14){
            return preg_match('/^\+234[1-9][0-9]{9}/', $value);
        }
        return preg_match('/^0[1-9][0-9]{9}/', $value);
    }

    function validEmail($value)
    {
        if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return true;
        }
        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute must be a valid email address or phone number';
    }
}
