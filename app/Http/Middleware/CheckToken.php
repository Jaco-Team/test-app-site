<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Api\Helper;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $module_name = explode('/', $request->path())[1];

        if( strlen($request->login['login']) == 0 ){
            abort(401, 'Unauthorized');
        }

        $my_info = Helper::getInfoByMy($request->login['login']);

        if( empty($my_info) ){
            abort(401, 'Unauthorized');
        }

        $check_acces = Helper::checkAccesModule($request->login['id'], $module_name);

        if( empty($check_acces) || (int)$check_acces->value == 0 ){
            abort(403, 'Forbidden');
        }

        return $next($request);
    }
}
