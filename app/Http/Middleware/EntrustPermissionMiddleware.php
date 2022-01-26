<?php

namespace App\Http\Middleware;

use Closure;
use Zizaco\Entrust\Middleware\EntrustPermission;

class EntrustPermissionMiddleware extends EntrustPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  Closure $next
     * @param  $permissions
     * @return mixed
     */
    public function handle($request, Closure $next, $permissions)
    {
        if (!is_array($permissions)) {
            $permissions = explode(self::DELIMITER, $permissions);
        }

        if ($this->auth->guest() || !$request->user()->can($permissions)) {
            if($request->expectsJson() || auth()->guard('admins')->check() || auth()->guard('users')->check()){
                return errorResponse('Permission Denied', 403, $request);
            }
            abort(403);
        }

        return $next($request);
    }
}
