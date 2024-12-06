<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\Helper;
use App\Http\Resources\GlobalResource;
use App\Models\Model_site_user_manager;
use Illuminate\Http\Request;

class Controller_site_user_manager extends Controller
{
    public function get_all(Request $request): GlobalResource
    {
      $my = Helper::getInfoByMy($request->login['login']);
      $points = Helper::getMyPointList($my->city_id, $my->point_id);

      $check = Model_site_user_manager::check($my->app_id);

      return new GlobalResource([
        'module_info' => $request->module_info,
        'check' => $check,
        'points' => $points
      ]);
    }

}
