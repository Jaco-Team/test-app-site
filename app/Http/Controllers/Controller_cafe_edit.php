<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\Helper;
use App\Http\Resources\GlobalResource;
use App\Models\Model_cafe_edit;
use Illuminate\Http\Request;

class Controller_cafe_edit extends Controller
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

    public function get_one(Request $request): GlobalResource
    {
      $cities = Model_cafe_edit::get_cities();
      $point_info = Model_cafe_edit::get_one_point($request->data['point_id']);

      $actual_time_list = Model_cafe_edit::get_actual_time_list($request->data['point_id'], date('N'));

      if(!empty($actual_time_list)){
        foreach($actual_time_list as $item){
          $item->time_start = date('H:i', strtotime($item->time_start));
          $item->time_end 	= date('H:i', strtotime($item->time_end));
        }
      }

      $dop_time_list = Model_cafe_edit::get_dop_time_list($request->data['point_id'], date('Y-m-d'));

      if(!empty($dop_time_list)){
        foreach($dop_time_list as $item){
          $item->time_start = date('H:i', strtotime($item->time_start));
          $item->time_end 	= date('H:i', strtotime($item->time_end));
        }
      }

      $zone = Model_cafe_edit::get_one_zone($request->data['point_id']);
      $other_zones = Model_cafe_edit::get_other_zones($request->data['city_id'], $request->data['point_id']);

      // если кафе не работает, причина закрытия кафе
      if ($point_info->cafe_handle_close == 0) { // 0 - по техническим причинам
        $point_info->is_сlosed_technic  =  1;
      } else if ($point_info->cafe_handle_close == 2) { // 2 - много заказов
        $point_info->is_сlosed_overload = 1;
      }

      $reason_list = array(
        ['id' => 1, 'name' => 'Нет света'],
        ['id' => 2, 'name' => 'Нет воды'],
        ['id' => 3, 'name' => 'Проблемы с интернететом']
      );

      return new GlobalResource([
        'cities' => $cities,
        'point_info' => $point_info,
        'actual_time_list' => $actual_time_list,
        'dop_time_list' => $dop_time_list,
        'zone' => $zone,
        'other_zones' => $other_zones,
        'reason_list' => $reason_list
      ]);
    }

    public function save_edit_point_info(Request $request): GlobalResource
    {
      if($request->data['addr'] == ''){
        return new GlobalResource([
          'st' => false,
          'text' => 'Адрес не указан'
        ]);
      }

      if($request->data['raion'] == ''){
        return new GlobalResource([
          'st' => false,
          'text' => 'Район не указан'
        ]);
      }

      if($request->data['city_id'] == 0){
        return new GlobalResource([
          'st' => false,
          'text' => 'Город не указан'
        ]);
      }

      if($request->data['organization'] == ''){
        return new GlobalResource([
          'st' => false,
          'text' => 'Организация не указана'
        ]);
      }

      if($request->data['inn'] == ''){
        return new GlobalResource([
          'st' => false,
          'text' => 'ИНН не указан'
        ]);
      }

      if($request->data['ogrn'] == 0){
        return new GlobalResource([
          'st' => false,
          'text' => 'ОГРН не указан'
        ]);
      }

      if($request->data['kpp'] == 0){
        return new GlobalResource([
          'st' => false,
          'text' => 'КПП не указан'
        ]);
      }

      if($request->data['full_addr'] == 0){
        return new GlobalResource([
          'st' => false,
          'text' => 'Полный адрес не указан'
        ]);
      }

      $url = 'https://jacochef.ru/api/v1/api_orders.php';

      $post_data = array (
        'type' => 'check_addr_full_web',
        'api_path' =>  $url,
        'city_id' 	=> $request->data['city_id'],
        'street'	=> $request->data['addr']
      );

      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      // указываем, что у нас POST запрос
      curl_setopt($ch, CURLOPT_POST, 1);
      // добавляем переменные
      curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);

      $output = curl_exec($ch);
      curl_close($ch);
      $response = json_decode( $output, true );

      if(!$response['st']){
        return new GlobalResource([
          'st' => false,
          'text' => $response['text']
        ]);
      }

      $my = Helper::getInfoByMy($request->login['login']);

      $request->data['addr'] = addslashes($request->data['addr']);
      $request->data['raion'] = addslashes($request->data['raion']);
      $request->data['organization'] = addslashes($request->data['organization']);
      $request->data['full_addr'] = addslashes($request->data['full_addr']);

      $res = Model_cafe_edit::update_point_info(
        $request->data['city_id'],
        $request->data['addr'],
        $request->data['raion'],
        $request->data['sort'],
        $request->data['organization'],
        $request->data['inn'],
        $request->data['ogrn'],
        $request->data['kpp'],
        $request->data['full_addr'],
        $request->data['is_active'],
        $request->data['phone_upr'],
        $request->data['phone_man'],
        $request->data['point_id']
      );

      if($res == 0 || $res > 0){

        Model_cafe_edit::insert_point_info_hist(
          $request->data['point_id'],
          $my->id,
          $request->data['city_id'],
          $request->data['addr'],
          $request->data['raion'],
          $request->data['sort'],
          $request->data['organization'],
          $request->data['inn'],
          $request->data['ogrn'],
          $request->data['kpp'],
          $request->data['full_addr'],
          $request->data['is_active'],
          $request->data['phone_upr'],
          $request->data['phone_man'],
          date('Y-m-d H:i:s')
        );

        return new GlobalResource([
          'st' => true,
          'text' => 'Успешно сохранено'
        ]);

      } else {

        return new GlobalResource([
          'st' => false,
          'text' => 'Ошибка обновления данных'
        ]);

      }
    }

    public function save_edit_point_sett(Request $request): GlobalResource
    {

      $my = Helper::getInfoByMy($request->login['login']);
      $point_info = Model_cafe_edit::get_point_info($request->data['point_id']);

      //новые статусы 1- работает / 0 - по техническим причинам / 2 - много заказов
      $active_cafe =  $request->data['cafe_handle_close'];
      $comment = '';

      if(!empty($request->data['cafe_handle_close'])){
        $active_cafe = 1;
      }

      $res = Model_cafe_edit::update_point_sett(
        $active_cafe,
        $request->data['cook_common_stol'],
        $request->data['summ_driver'],
        $request->data['summ_driver_min'],
        $request->data['priority_order'],
        $request->data['priority_pizza'],
        $request->data['rolls_pizza_dif'],
        $request->data['point_id']
      );

      if((int)$point_info->cafe_handle_close != $active_cafe  &&  $active_cafe == 1){
        Model_cafe_edit::update_cafe_close_history(date('Y-m-d H:i:s'), $request->data['point_id']);
        Model_cafe_edit::insert_event_new_hist($my->id, $request->data['point_id'], date('Y-m-d'), $comment);
      }

      if($res == 0 || $res > 0){

        Model_cafe_edit::insert_point_sett_hist(
          $request->data['point_id'],
          $my->id,
          $active_cafe,
          $request->data['cook_common_stol'],
          $request->data['summ_driver'],
          $request->data['summ_driver_min'],
          $request->data['priority_order'],
          $request->data['priority_pizza'],
          $request->data['rolls_pizza_dif'],
          date('Y-m-d H:i:s')
        );

        return new GlobalResource([
          'st' => true,
          'text' => 'Успешно сохранено'
        ]);

      } else {

        return new GlobalResource([
          'st' => false,
          'text' => 'Ошибка обновления данных'
        ]);

      }
    }

    public function save_edit_point_rate(Request $request): GlobalResource
    {
      $my = Helper::getInfoByMy($request->login['login']);

      Model_cafe_edit::delete_point_rate($request->data['point_id']);

      $res = Model_cafe_edit::insert_point_rate(
        $request->data['point_id'],
        $request->data['date_start'],
        $request->data['k_pizza'],
        $request->data['k_pizza_kux'],
        $request->data['k_rolls_kux']
      );

      Model_cafe_edit::insert_point_rate_hist(
        $request->data['point_id'],
        $my->id,
        $request->data['date_start'],
        $request->data['k_pizza'],
        $request->data['k_pizza_kux'],
        $request->data['k_rolls_kux'],
        date('Y-m-d H:i:s')
      );

      if((int)$res > 0) {

        return new GlobalResource([
          'st' => true,
          'text' => 'Изменения вступят в действие с указанной даты'
        ]);

      } else {

        return new GlobalResource([
          'st' => false,
          'text' => 'Ошибка обновления данных'
        ]);

      }

    }

    public function save_edit_point_pay(Request $request): GlobalResource
    {

      // эти функции запускать по крону с даты начала изменений ??

      //      $date = explode('-', date('Y-m-d'));
      //      //$min_date = $date;
      //      $this_d = (int)$date[2];
      //      $this_date = $date[0].'-'.$date[1].'-';
      //
      //      if( $this_d >= 1 && $this_d < 16 ){
      //        $this_date .= '01';
      //        //$max_day = $min_date[0].'-'.$min_date[1].'-15';
      //      } else {
      //        $this_date .= '16';
      //        //$max_day = date("Y-m-t", strtotime( $min_date[0].'-'.$min_date[1].'-'.$min_date[2] ));
      //      }
      // update_dir_price
      // update_driver_price

      $my = Helper::getInfoByMy($request->login['login']);

      Model_cafe_edit::delete_point_pay($request->data['point_id']);

      $res = Model_cafe_edit::insert_point_pay(
        $request->data['point_id'],
        $request->data['date_start'],
        $request->data['dir_price'],
        $request->data['price_per_lv'],
        $request->data['driver_price']
      );

      Model_cafe_edit::insert_point_pay_hist(
        $request->data['point_id'],
        $my->id,
        $request->data['date_start'],
        $request->data['dir_price'],
        $request->data['price_per_lv'],
        $request->data['driver_price'],
        date('Y-m-d H:i:s')
      );

      if((int)$res > 0) {

        return new GlobalResource([
          'st' => true,
          'text' => 'Изменения вступят в действие с указанной даты'
        ]);

      } else {

        return new GlobalResource([
          'st' => false,
          'text' => 'Ошибка обновления данных'
        ]);

      }

    }
}
