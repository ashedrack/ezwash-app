<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidPhone implements Rule
{
    protected $isRequired;

    /**
     * ValidPhone constructor.
     *
     * @param bool $isRequired
     * @return void
     */
    public function __construct($isRequired = true)
    {
        $this->isRequired = $isRequired;
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
        if(!$this->isRequired && empty($value)) {
            return true;
        }elseif(strlen($value) === 14) {
            return preg_match('/^\+234[1-9][0-9]{9}/', $value);
        }
        return preg_match('/^0[1-9][0-9]{9}/', $value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute must be valid';
    }
}
