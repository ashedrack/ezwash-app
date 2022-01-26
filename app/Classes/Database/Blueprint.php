<?php

namespace App\Classes\Database;

use Illuminate\Database\Schema\Blueprint as BaseBlueprint;

class Blueprint extends BaseBlueprint {

    /**
     * Add nullable creation and update timestamps to the table.
     *
     * @param int  $precision
     * @param boolean $useCurrent
     * @return void
     */
    public function timestamps($precision = 0, $useCurrent = true)
    {
        if($useCurrent){
            $this->timestamp('created_at', $precision)->useCurrent();

            $this->timestamp('updated_at', $precision)->nullable()->useCurrent();
        } else {
            $this->timestamp('created_at', $precision)->nullable();

            $this->timestamp('updated_at', $precision)->nullable();
        }
    }

}
