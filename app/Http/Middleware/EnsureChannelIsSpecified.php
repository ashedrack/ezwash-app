<?php

namespace App\Http\Middleware;

use Closure;

class EnsureChannelIsSpecified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param array $channels
     * @return mixed
     */
    public function handle($request, Closure $next, ...$channels)
    {
        $expectedChannels = (!empty($channels)) ? $channels : ['customer_app', 'tablet_app', 'web_crm'];

        //Reject request if invalid or no channel is set in the request header or body;
        $requestChannel = ($request->header('channel')) ? $request->header('channel') : $request->get('channel');
        if(!in_array($requestChannel, $expectedChannels)){
            return errorResponse('Access forbidden: invalid request channel', 403, $request);
        }
        return $next($request);
    }
}
