<?php

namespace App\Http\Middleware;

use App\Models\RequestLog;
use App\Models\User;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Context;
use Symfony\Component\HttpFoundation\Response;

class LogAfterRequestMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Context::has('request_log_id')){
            return $next($request);
        }
        
        $response = $next($request);
        
        $body = $request->all();
        if (! is_null($request->password)) {
            $body = $request->all();
            unset($body['password']);
        }
        
        if (! $response instanceof JsonResponse) {
            $status = 200;
        } else {
            $status = $response->status();
        }

        RequestLog::createOrUpdate(
            Context::get('request_log_id'),
            $request->getUri(),
            $request->route()->getName(),
            $request->getMethod(),
            json_encode($body),
            $request->path(),
            $status,
            $request->userAgent(),
            Auth::user(),
        );

		return $response;
    }
}
