<?php

namespace App\Http\Middleware;

use App\Models\Permission;
use Closure;

class VerifyUserRoutePermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $routeName = $request->route()->getName();
        $permissionExists = Permission::where('name', $routeName)->exists();
        if(($permissionExists && $request->user()->can($routeName)) || !$permissionExists ){
            return $next($request);
        }
        return abort(403);
    }
}
