<?php

namespace App\Traits;

trait CoreModelMethods
{

    public function _isActive(){
        if($this->is_active === 1) {
            return true;
        }
        return false;
    }

    public function deactivate()
    {
        $this->update([
            'is_active' => 0
        ]);
        $this->fireModelEvent('deactivated', false);
    }


    public function activate()
    {
        $this->update([
            'is_active' => 1
        ]);
        $this->fireModelEvent('activated', false);
    }

}
