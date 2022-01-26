<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

class JwtMiddleware extends BaseMiddleware
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
        try {
            $this->authenticate($request);
        } catch (UnauthorizedHttpException $e) {
            if ($e->getPrevious() instanceof TokenExpiredException) {
                return errorResponse('Token expired', 401, $request);
            } else if ($e->getPrevious() instanceof TokenInvalidException) {
                return errorResponse('Token invalid', 401, $request);
            } else if ($e->getPrevious() instanceof TokenBlacklistedException) {
                return errorResponse('Token invalid: blacklisted', 401, $request);
            } else {
                return errorResponse('Token not Provided', 403, $request);
            }
        } catch (Exception $e){
            return errorResponse('Something went wrong', 500, $request, $e);
        }
        return $next($request);
    }
}
