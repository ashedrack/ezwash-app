<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Builder;

class LocationScope implements Scope {

    public function apply(Builder $builder, Model $model)
    {
        $builder->whereNull('location_id')
            ->orWhereHas('location', function ($q){
                $q->whereNull('deleted_at');
            });
    }
}
