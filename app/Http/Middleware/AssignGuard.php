<?php

namespace App\Http\Middleware;

use App\Models\Location;
use Closure;

class AssignGuard
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if($guard != null)
            auth()->shouldUse($guard);

        $authUser = auth($guard)->user();
        if(!empty($authUser) && !$authUser->is_active){
            return errorResponse('Your account is inactive, please contact the admin to activate it.', 400, $request);
        }
        if(!empty($authUser) && $guard === 'admins'){
            if(!is_null($authUser->location_id)) {
                session(['authLocation' => $authUser->location]);
                session(['authAdmin' => $authUser]);
                return $next($request);
            }
            $requestLocation = ($request->header('store_location')) ? $request->header('store_location') : $request->get('store_location');
            if(is_null($requestLocation)){
                return errorResponse('store_location not specified', 400, $request);
            }
            $location = ($authUser->_isOverallAdmin()) ?
                Location::find($requestLocation) :
                Location::where('company_id', $authUser->company_id)->where('id', $requestLocation)->first();
            if(empty($location)){
                return errorResponse('Invalid store_location provided', 400, $request);
            }
            session(['authLocation' => $location]);
        }
        return $next($request);
    }
}
