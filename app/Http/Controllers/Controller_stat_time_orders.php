<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\Helper;
use App\Http\Resources\GlobalResource;
use App\Models\Model_stat_time_orders;
use Illuminate\Http\Request;

class Controller_stat_time_orders extends Controller
{
    public function get_all(Request $request): GlobalResource
    {
       $my = Helper::getInfoByMy($request->login['login']);
       $points = Helper::getMyPointList($my->city_id, $my->point_id);

      return new GlobalResource([
        'module_info' => $request->module_info,
        'points' => $points
      ]);
    }

    public function get_stat(Request $request): GlobalResource
    {
      $user_info = Helper::getInfoByMy($request->login['login']);

      if(
        $user_info->app_type == 'cook' ||
        $user_info->app_type == 'kassir' ||
        $user_info->app_type == 'manager' ||
        $user_info->app_type == 'other' ||
        $user_info->app_type == 'driver'
      ){
        $check_date = date('Y-m-d', time()-86400*3);

        if($request->data['date'] < $check_date ){
          return array(
            'users' => [],
            'hours' => [],
            'orders' => [],
            'full_time_orders' => [],
            'all_load_time' => [],
            'all_povar_time' => [],
            'all_kassit_time' => [],
            'all_kassit_time_' => [],
            'all_work_time' => [],
            'all_pf_work_time' => [],
          );
        }
      }

      $base = Helper::get_base($request->data['point_id']);

      $date = explode('-', $request->data['date']);
      $this_d = (int)$date[2];
      $this_date = $date[0].'-'.$date[1].'-';

      if( $this_d >= 1 && $this_d < 16 ){
        $this_date .= '01';
      }else{
        $this_date .= '16';
      }

      $unics_users = Model_stat_time_orders::get_unics_users($base, $request->data['date']);
      $unics_hours = Model_stat_time_orders::get_unics_hours($base, $request->data['date']);

      foreach($unics_users as $user){
        $user->all_time = Model_stat_time_orders::get_unics_users_all_time($base, $request->data['date'], $user->user_id);
        $user->all_time_all = Model_stat_time_orders::get_unics_users_all_time_all($base, $request->data['date'], $user->user_id);
        $user->povar_time = Model_stat_time_orders::get_unics_users_povar_time($base, $request->data['date'], $user->user_id);
        $user->povar_time_all = Model_stat_time_orders::get_unics_users_povar_time_all($base, $request->data['date'], $user->user_id);
        $user->kassir_time = Model_stat_time_orders::get_unics_users_kassir_time($base, $request->data['date'], $user->user_id);
        $user->kassir_time_all = Model_stat_time_orders::get_unics_users_kassir_time_all($base, $request->data['date'], $user->user_id);
        $user->work_time = Model_stat_time_orders::get_unics_users_work_time($base, $request->data['date'], $user->user_id);
        $user->work_time_all = Model_stat_time_orders::get_unics_users_work_time_all($base, $request->data['date'], $user->user_id);
        $user->pf_work_time = Model_stat_time_orders::get_unics_users_pf_work_time($base, $request->data['date'], $user->user_id);
        $user->pf_work_time_all = Model_stat_time_orders::get_unics_users_pf_work_time_all($base, $request->data['date'], $user->user_id);
      }

      foreach($unics_users as $user){
        foreach($user->all_time as $time){
          foreach($unics_hours as $h){
            if((int)$h->h == (int)$time->h){
              $h->all_time_sec += (int)$time->time;
            }
          }
        }

        foreach($user->povar_time as $time){
          foreach($unics_hours as $h){
            if((int)$h->h == (int)$time->h){
              $h->povar_time_sec += (int)$time->time;
            }
          }
        }

        foreach($user->kassir_time as $time){
          foreach($unics_hours as $h){
            if((int)$h->h == (int)$time->h){
              $h->kassir_time_sec += (int)$time->time;
            }
          }
        }

        foreach($user->work_time as $time){
          foreach($unics_hours as $h){
            if((int)$h->h == (int)$time->h){
              $h->work_time_sec += (int)$time->time;
            }
          }
        }

        foreach($user->pf_work_time as $time){
          foreach($unics_hours as $h){
            if((int)$h->h == (int)$time->h){
              $h->pf_work_time_sec += (int)$time->time;
            }
          }
        }
      }

      foreach($unics_hours as $user){
        $user->all_time_h = Model_stat_time_orders::convert_time($user->all_time_sec);
        $user->povar_time_h = Model_stat_time_orders::convert_time($user->povar_time_sec);
        $user->kassir_time_h = Model_stat_time_orders::convert_time($user->kassir_time_sec);
        $user->work_time_h = Model_stat_time_orders::convert_time($user->work_time_sec);
        $user->pf_work_time_h = Model_stat_time_orders::convert_time($user->pf_work_time_sec);
      }

      $all_load_time = 0;
      $all_povar_time = 0;
      $all_kassit_time = 0;
      $all_work_time = 0;
      $all_pf_work_time = 0;

      foreach($unics_users as $user){

        foreach($user->all_time as $time){
          $all_load_time += (int)$time->time;
          $time->time_h = Model_stat_time_orders::convert_time($time->time);
        }

        $user->all_time_all = Model_stat_time_orders::convert_time($user->all_time_all->time ?? 0);

        foreach($user->povar_time as $time){
          $all_povar_time += $time->time;
          $time->time_h = Model_stat_time_orders::convert_time($time->time);
        }

        $user->povar_time_all = Model_stat_time_orders::convert_time($user->povar_time_all->time ?? 0);

        foreach($user->kassir_time as $time){
          $all_kassit_time += $time->time;
          $time->time_h = Model_stat_time_orders::convert_time($time->time);
        }

        $user->kassir_time_all_ = $user->kassir_time_all->time ?? 0;
        $user->kassir_time_all = Model_stat_time_orders::convert_time($user->kassir_time_all->time ?? 0);

        foreach($user->work_time as $time){
          $all_work_time += $time->time;
          $time->time_h = Model_stat_time_orders::convert_time($time->time);
        }

        $user->work_time_all = Model_stat_time_orders::convert_time($user->work_time_all->time ?? 0);

        foreach($user->pf_work_time as $time){
          $all_pf_work_time += $time->time;
          $time->time_h = Model_stat_time_orders::convert_time($time->time);
        }

        $user->pf_work_time_all = Model_stat_time_orders::convert_time($user->pf_work_time_all->time ?? 0);
      }

      $queue_full = Helper::curl_api(array(
        'api_path' => 'https://jacochef.ru/api/v1/api_order_queue.php',
        'type' => 'get_orders_queue_by_time_wait_full',
        'point_id' => $request->data['point_id'],
        'date' => $request->data['date'],
        'time' => date('H:i:s'),
      )) ?? [];

      $arr_h = [];

      foreach($queue_full as $order){
        $arr_h[] = explode(':', $order['time_start'])[0];
      }

      $arr_h = array_unique($arr_h);

      foreach($arr_h as $key => $h){
        $arr_h[$key] = array(
          'h' => $h,
          'full_time' => 0,
          'count_users' => 0,
          'time_orders' => 0
        );
      }

      $full_time_orders = 0;

      foreach($queue_full as $order){
        $h_ = explode(':', $order['time_start'])[0];

        $full_time_orders += (float)$order['full_time'];

        foreach($arr_h as $key => $h){
          if( $h['h'] == $h_ ){
            $arr_h[$key]['full_time'] += (float)$order['full_time'];
            $arr_h[$key]['count_users'] = (int)$order['count_users'];
            $arr_h[$key]['time_orders'] += ROUND((float)$order['time_order'], 2);
          }
        }
      }

      foreach($arr_h as $key => $h){
        $arr_h[$key]['time_h'] = Model_stat_time_orders::convert_time($h['full_time']);
      }

      $full_time_orders = Model_stat_time_orders::convert_time($full_time_orders);

      $all_load_time = Model_stat_time_orders::convert_time($all_load_time);
      $all_povar_time = Model_stat_time_orders::convert_time($all_povar_time);
      $all_kassit_time_ = $all_kassit_time;
      $all_kassit_time = Model_stat_time_orders::convert_time($all_kassit_time);

      $all_work_time = Model_stat_time_orders::convert_time($all_work_time);
      $all_pf_work_time = Model_stat_time_orders::convert_time($all_pf_work_time);

      foreach($arr_h as $hours){
        $check = false;

        foreach($unics_hours as $stat){
          if((int)$stat->h == (int)$hours['h']){
            $check = true;
          }
        }

        if(!$check){
          $unics_hours[] = (object)array(
            'h' => (int)$hours['h'],
            'all_time_sec' => 0,
            'povar_time_sec' => 0,
            'kassir_time_sec' => 0,
            'work_time_sec' => 0,
            'pf_work_time_sec' => 0,
            'all_time_h' => '00:00',
            'povar_time_h' => '00:00',
            'kassir_time_h' => '00:00',
            'work_time_h' => '00:00',
            'pf_work_time_h' => '00:00',
          );
        }
      }

      $arr_h_new = [];

      foreach($arr_h as $h){
        $arr_h_new[] = $h;
      }

      $collection = collect($unics_hours);
      $sorted = $collection->sortBy('h');
      $unics_hours = $sorted->values()->all();

      foreach($unics_hours as $h ){
        $res = Model_stat_time_orders::get_time_queue_orders($base, $h->h);
        $h->wait = $res->time_min ?? 0;
      }

      return new GlobalResource([
        'users' => $unics_users,
        'hours' => $unics_hours,
        'orders' => $arr_h_new,
        'full_time_orders' => $full_time_orders,
        'all_load_time' => $all_load_time,
        'all_povar_time' => $all_povar_time,
        'all_kassit_time' => $all_kassit_time,
        'all_kassit_time_' => $all_kassit_time_,
        'all_work_time' => $all_work_time,
        'all_pf_work_time' => $all_pf_work_time,
        '$queue_full' => $queue_full
      ]);
    }

}
