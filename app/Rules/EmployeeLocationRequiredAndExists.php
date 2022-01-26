<?php

namespace App\Rules;

use App\Models\Location;
use App\Models\Role;
use Illuminate\Contracts\Validation\Rule;

class EmployeeLocationRequiredAndExists implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    protected $roles;
    protected $errorMessage;
    public function __construct($roles = null)
    {
        if(!$roles){
            $roles = Role::get()->pluck('hierarchy', 'name')->toArray();
        }
        $this->roles = $roles;
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
        $requiredHierarchyForIgnore = $this->roles['super_admin'];
        $highestRoles = Role::whereIn('id', request('roles'))->max('hierarchy');

        //Check if the highest hierarchy assigned to the employee does not require a location
        if($highestRoles < $requiredHierarchyForIgnore){
            if(!$value){
                $this->errorMessage = 'Location field is required';
                return false;
            }
            elseif(empty(Location::find($value))){
                $this->errorMessage = 'Invalid location selected';
                return false;
            }
        }
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        if(!$this->errorMessage) {
            return 'Invalid :attribute selected';
        }
        else{
            return $this->errorMessage;
        }
    }
}
