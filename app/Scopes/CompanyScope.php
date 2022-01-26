<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Builder;

class CompanyScope implements Scope {

    public function apply(Builder $builder, Model $model)
    {
        $builder->whereNull('company_id')
            ->orWhereHas('company', function ($q){
                $q->whereNull('deleted_at');
            });
    }
}
