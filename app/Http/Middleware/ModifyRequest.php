<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Api\Helper;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Context;

class ModifyRequest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $module_name = explode('/', $request->path())[1];

        if( strlen($request->login) == 0 ) {
            $request->login = [
                'login' => '',
                'id' => ''
            ];
        }else{
            $request->login = Helper::parseToken($request->login);
        }

        $request->data = json_decode($request->data, true);
        $request->module_info = Helper::getInfoModule($module_name);
        Context::add('url', $request->url());

        return $next($request);
    }
}
