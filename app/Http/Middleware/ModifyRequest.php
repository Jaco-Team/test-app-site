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
     *
     */
    public function handle(Request $request, Closure $next): Response
    {
        $module_name = explode('/', $request->path());

        if( count($module_name) > 1 ){
            $module_name = $module_name[1];

            $request->module_info = Helper::getInfoModule($module_name);


        }else{
            $request->module_info = null;
        }

        if( strlen($request->login) == 0 ) {
            $request->login = [
                'login' => '',
                'id' => ''
            ];
        }else{
            $request->login = Helper::parseToken($request->login);
            $request->login['full'] = Helper::getInfoByMy($request->login['login']);
            $request->access = Helper::getDopAccessModule($request->login['full']['app_id'], $module_name);
        }

        $request->data = json_decode($request->data, true);
        Context::add('url', $request->url());

        return $next($request);
    }
}
