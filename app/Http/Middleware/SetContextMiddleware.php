<?php

namespace App\Http\Middleware;

use App\Models\RequestLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;
use Symfony\Component\HttpFoundation\Response;

class SetContextMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $route = app('router')->getRoutes()->match(app('request')->create($request->path(), $request->getMethod()))->getName();
        
        if (RequestLog::isExcluded($route)){
            return $next($request);
        }

        $requestLog = new RequestLog();
        $requestLog->save();
        Context::add('request_log_id', $requestLog->id);
        
        return $next($request);
    }
}
