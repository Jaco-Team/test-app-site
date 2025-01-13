<?php

namespace App\Http\Controllers;

use App\Http\Resources\GlobalResource;
use App\Http\Controllers\Api\Helper;
use App\Models\Model_module_stat_order;
use DateInterval;
use DatePeriod;
use Illuminate\Http\Request;
use DateTime;

class Controller_module_stat_order extends Controller
{
    public function get_all(Request $request): GlobalResource
    {
      $my = Helper::getInfoByMy($request->login['login']);
      $points = Helper::getMyPointList($my->city_id, $my->point_id);

      $metrics = array(
        ['id' => 1, 'name' => 'Средний чек'],
        ['id' => 2, 'name' => 'Выручка'],
        ['id' => 3, 'name' => 'Кол-во заказов']
      );

      return new GlobalResource([
        'module_info' => $request->module_info,
        'points' => $points,
        'metrics' => $metrics,
      ]);
    }

  /**
   * @throws \Exception
   */
    public function get_stat_days(Request $request): GlobalResource
    {

      $search_data = '';
      $stat_days = [];

      if(!empty($request->data['avg']) && $request->data['avg'] > 0){
        $search_data = $search_data . 'ROUND(AVG(IF(o.`free_drive`=1,
						IF(o.`summ_promo`=0, 1, o.`summ_promo`),
						o.`summ_promo`+o.`summ_div`
					)), 0) as avg_orders,';
      }

      if(!empty($request->data['summ']) && $request->data['summ'] > 0){
        $search_data = $search_data . 'SUM(IF(o.`free_drive`=1,
						IF(o.`summ_promo`=0, 1, o.`summ_promo`),
						o.`summ_promo`+o.`summ_div`
					)) as sum_orders,';
      }

      if(!empty($request->data['count']) && $request->data['count'] > 0){
        $search_data = $search_data . 'COUNT(*) as count_orders';
      }

      $search_data = rtrim($search_data, ',');

      $from = new DateTime($request->data['date_start']);
      $to = new DateTime(date("Y-m-d", strtotime($request->data['date_end']. "+1 day")));
      $period = new DatePeriod($from, new DateInterval('P1D'), $to);

      $arrayOfDates = array_map(
        function ($item) {
          return $item->format('Y-m-d');
        },
        iterator_to_array($period)
      );

      if(!empty($request->data['point']) && $search_data && !empty($arrayOfDates)){
        foreach($request->data['point'] as $point){

          foreach($arrayOfDates as $date) {
            $res = Model_module_stat_order::get_stat_days($search_data, $point['base'], $date);
            if(!empty($request->data['is_akcii']) && $request->data['is_akcii'] > 0){
              $avd = Model_module_stat_order::get_point_adv_days($point['id'], $date);
              $res->avd = $avd;
            }
            $res->date = $date;
            $stat_days[] = $res;
          }

        }
      }

      return new GlobalResource([
        'stat' => $stat_days,
      ]);
    }

    public function get_stat_month(Request $request): GlobalResource
    {

      $search_data = '';
      $stat_month = [];

      if(!empty($request->data['avg']) && $request->data['avg'] > 0){
        $search_data = $search_data . 'ROUND(AVG(IF(o.`free_drive`=1,
              IF(o.`summ_promo`=0, 1, o.`summ_promo`),
              o.`summ_promo`+o.`summ_div`
            )), 0) as avg_orders,';
      }

      if(!empty($request->data['summ']) && $request->data['summ'] > 0){
        $search_data = $search_data . 'SUM(IF(o.`free_drive`=1,
              IF(o.`summ_promo`=0, 1, o.`summ_promo`),
              o.`summ_promo`+o.`summ_div`
            )) as sum_orders,';
      }

      if(!empty($request->data['count']) && $request->data['count'] > 0){
        $search_data = $search_data . 'COUNT(*) as count_orders';
      }

      $search_data = rtrim($search_data, ',');

      $end = date('Y-m-t', strtotime($request->data['date_end'] . '-01'));
      $to = new DateTime(date("Y-m-d", strtotime($end)));
      $from = new DateTime($request->data['date_start']);
      $period = new DatePeriod($from, new DateInterval('P1M'), $to);

      $arrayOfDates = array_map(
        function ($item) {
          return $item->format('Y-m');
        },
        iterator_to_array($period)
      );

      $one_new = [];
      $lastKey = count($arrayOfDates) - 1;

      foreach ($arrayOfDates as $index => $date) {
        if ($index === 0 && count($arrayOfDates) > 1) {
          $one_new[] = [
            'date_start' => $request->data['date_start'],
            'date_end' => date('Y-m-t', strtotime($request->data['date_start'] . '-01'))
          ];
        } else if ($index === $lastKey) {
          $one_new[] = [
            'date_start' => date('Y-m-01', strtotime($request->data['date_end'] . '-01')),
            'date_end' => $request->data['date_end']
          ];
        } else {
          $one_new[] = [
            'date_start' => date('Y-m-01', strtotime($date . '-01')),
            'date_end' => date('Y-m-t', strtotime($date . '-01'))
          ];
        }
      }

      if(!empty($request->data['point']) && $search_data && !empty($one_new)){
        foreach($request->data['point'] as $point){
          foreach($one_new as $date) {
            $res = Model_module_stat_order::get_stat_month($search_data, $point['base'], $date['date_start'], $date['date_end']);
            if(!empty($request->data['is_akcii']) && $request->data['is_akcii'] > 0){
              $avd = Model_module_stat_order::get_point_adv_month($point['id'], $date['date_start'], $date['date_end']);
              $res->avd = $avd;
            }
            $res->date = date("Y-m",strtotime($date['date_start']));
            $stat_month[] = $res;
          }
        }
      }

      return new GlobalResource([
        'stat' => $stat_month
      ]);
    }

}
