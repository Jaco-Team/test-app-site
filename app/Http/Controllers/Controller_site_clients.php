<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\Helper;
use App\Http\Resources\GlobalResource;
use App\Models\Model_site_clients;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\Http\Controllers\Api\SiteClientsExport;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class Controller_site_clients extends Controller
{
    public function get_all(Request $request): GlobalResource
    {
      $module_id = 189;
      $my = Helper::getInfoByMy($request->login['login']);

      $acces = Model_site_clients::get_acces($my->app_id, $module_id);;
      $new_acces = [];

      foreach($acces as $acc){
        $new_acces[$acc->param] = $acc->value;
      }

      $cities = Model_site_clients::get_all_cities();
      $all_items = Model_site_clients::get_all_items();

      return new GlobalResource([
        'module_info' => $request->module_info,
        'acces' => $new_acces,
        'cities' => $cities,
        'all_items' => $all_items,
      ]);
    }

    public function get_clients(Request $request): GlobalResource
    {
      if(empty($request->data['search']) || strlen($request->data['search']) < 4) {

        return new GlobalResource([
          'st' => false,
          'text' => 'Необходимо указать минимум 4 цифры из номера телефона',
        ]);

      }

      $check_login = '`login` LIKE "%'.$request->data['search'].'%"';
      $clients = Model_site_clients::get_site_clients($check_login);

      return new GlobalResource([
        'st' => true,
        'clients' => $clients,
      ]);
    }

    public function get_orders(Request $request): GlobalResource
    {

      $city_id = '';
      $search_data = '';
      $item_id = '';
      $promo = '';

      if( strlen($request->data['promo']) > 0 ) {
        $promo = Model_site_clients::get_promo_by_name($request->data['promo']);
      }else{
        $promo = '""';
      }

      if(count($request->data['city_id']) > 0){
        foreach($request->data['city_id'] as $city){
          $city_id = $city_id . ', ' . $city['id'];
        }

        $city_id = trim($city_id, ', ');
        $city_id = " AND `city_id` IN ($city_id)";
      }

      if(count($request->data['items']) > 0){
        foreach($request->data['items'] as $item){
          $item_id = $item_id . ', ' . $item['id'];
        }

        $item_id = trim($item_id, ', ');
        $item_id = "WHERE oi.`item_id` IN ($item_id)";
      }

      if(strlen($request->data['order']) > 0) {
        $search_data = $search_data . ' AND o.`id` LIKE "'.$request->data['order'].'"';
      }

      if(strlen($request->data['number']) > 0) {
        $search_data = $search_data . ' AND o.`number` LIKE "%'.$request->data['number'].'%"';
      }

      if(strlen($request->data['addr']) > 0) {
        $string = trim($request->data['addr']);
        $address = mb_strtolower($string, 'UTF-8');
        $search_data = $search_data . ' AND (LOWER(o.`home`) LIKE "%'.$address.'%" OR LOWER(o.`street`) LIKE "%'.$address.'%")';
      }

      $points = Model_site_clients::get_points($city_id);

      $search_orders = [];

      if( strlen($request->data['date_start']) == 0 ){
        $request->data['date_start'] = '2000-01-01';
      }

      if( strlen($request->data['date_end']) == 0 ){
        $request->data['date_end'] = date('Y-m-d', time() + 86400 * 7 * 3);
      }

      if(count($points) > 0){
        foreach($points as $point){
          $orders = Model_site_clients::get_orders($point->id, $point->addr, $point->base, $request->data['date_start'], $request->data['date_end'], $search_data, $item_id, $promo);

          $search_orders = array_merge($search_orders, $orders);
        }
      }

      $orders = Collection::make($search_orders);

      $orders = $orders->pipeThrough([
        function (Collection $collection) {
          return $collection->map(function (object $order) {
            if ((int)$order->client_id == (int)$order->user_id) {
              $order->type_user = 'Клиент';
            } else {
              if (((int)$order->client_id >= 0 || (int)$order->client_id == -28) && ((int)$order->type_origin == 1 || (int)$order->type_origin == 2)) {
                $order->type_user = 'Контакт-центр';
              } else {
                if ((int)$order->type_origin == 3 || (int)$order->type_origin == 4) {
                  $order->type_user = 'Кухня';
                }
              }
            };

            return $order;
          });
        },
        function (Collection $collection) {
          return $collection->map(function (object $order) {
            $order_time = explode(':', $order->date_time_order);
            $pre_order_time = explode(':', $order->date_time_preorder);
            $close_time_order = $order->close_order ? explode(':', $order->close_order) : explode(':', '00:00:00');
            $date_time_delete = $order->date_time_delete ? explode(':', $order->date_time_delete) : explode(':', '00:00:00');
            $start_time = '';
            $close_time = '';

            if($order->date_time_preorder == '00:00:00'){
              //обычный
              $start_time = (int)$order_time[0]*60+(int)$order_time[1]+(int)$order_time[2];
            }else{
              //предзаказ
              $start_time = (int)$pre_order_time[0]*60+(int)$pre_order_time[1];
            }


            if((int)$order->is_delete == 1){
              $close_time = (int)$date_time_delete[0]*60+(int)$date_time_delete[1];
            }else {
              if ((int)$order->status_order == 6) {
                //готовые
                $close_time = (int)$close_time_order[0] * 60 + (int)$close_time_order[1];
              } else {
                //не готовые
                $close_time = (int)date('H') * 60 + (int)date('i');
              }
            }

            $order->time = ($close_time - $start_time) > 0 ? $close_time - $start_time : '';

            return $order;
          });
        },
        function (Collection $collection) {
          return $collection->map(function (object $order) {
            $time = date('H:i');
            $max_time = time();

            $dop_time = (int)$order->plus_time;

            if( (int)$order->is_preorder == 1 ){
              $max_time = $order->unix_time + $dop_time*60;
              $time = date('H:i', $order->unix_time) . ' - ' . date('H:i', $order->unix_time + $dop_time*60);
            }else{
              $client_time = explode('-', $order->unix_time_to_client);

              if( count($client_time) > 1 && (int)$client_time[1] > 0 ){
                $max_time = $order->unix_time + (int)$client_time[1] * 60;

                $time = date('H:i', (int)$order->unix_time + (int)$client_time[0] * 60) . ' - ' . date('H:i', (int)$order->unix_time + (int)$client_time[1] * 60);
              }else{
                $client_time = 60;

                $max_time = $order->unix_time + $client_time * 60;

                $time = date('H:i', $order->unix_time) . ' - ' . date('H:i', $order->unix_time + $client_time * 60);
              }
            }

            $order->need_time = $time;
            $order->to_time_sec = $max_time - time();

            if( (int)$order->status_order < 6 ){
              $order->to_time = (new Controller_concenter)->sec_to_time($order->to_time_sec);
            }else{
              $order->to_time = '';
            }

            return $order;
          });
        },
      ]);

      return new GlobalResource([
        'search_orders' => $orders
      ]);
    }

    public function get_one_client(Request $request): GlobalResource
    {
      $client_info = Model_site_clients::get_client_info($request->data['login']);
      $err_orders = Model_site_clients::get_client_err_orders($request->data['login']);
      $client_comments= Model_site_clients::get_client_comments($request->data['login']);
      $client_login_sms= Model_site_clients::get_client_send_sms($client_info->id);
      $client_login_yandex= Model_site_clients::get_client_login_yandex($request->data['login']);

      $all_points = Model_site_clients::get_all_points();
      $client_orders = [];

      foreach($all_points as $point){
        $client_order = Model_site_clients::get_client_orders($point->base, $point->id, $request->data['login']);

        $client_orders = array_merge($client_orders, $client_order);
      }

      $collection_orders_client = collect($client_orders);

      $sorted_collection_orders_client = $collection_orders_client->sortByDesc('date_time');

      return new GlobalResource([
        'st' => true,
        'client_info' => $client_info,
        'all_points' => $all_points,
        'client_orders' => $sorted_collection_orders_client->values()->all(),
        'err_orders' => $err_orders,
        'client_comments' => $client_comments,
        'client_login_sms' => $client_login_sms,
        'client_login_yandex' => $client_login_yandex
      ]);
    }

    public function get_one_order(Request $request): GlobalResource
    {
      $point_id = $request->data['point_id'];
      $order_id = $request->data['order_id'];

      $base = Helper::get_base($point_id);

      $city_name = Model_site_clients::get_city_name($point_id)->name;
      $err_order = Model_site_clients::get_err_order($point_id, $order_id);
      $items_order = Model_site_clients::get_items_order($base, $order_id);
      $order = Model_site_clients::get_order($base, $order_id, $point_id, date('Y-m-d H:i:s'), date('Y-m-d'), $city_name);

      if((int)$order->is_preorder == 1){

        $time = explode(' ', $order->date_time_preorder_or)[1];
        $time = explode(':', $time);

        $order->date_time_pred = [
          'date' => explode(' ', $order->date_time_preorder_or)[0],
          'time' => $time[0].':'.$time[1]
        ];
      }

      $status = (int)$order->status_order;
      $status_order = '';

      if((int)$order->type_order_ == 1) {
        if($status == 1) {
          $status_order = 'Оформлен';
        }

        if($status > 1 && $status <= 4) {
          $status_order = 'Готовится';
        }

        if($status == 5) {
          $status_order = 'У курьера';
        }

        if($status == 6) {
          $status_order = 'Доставлен';
        }
      }

      if((int)$order->type_order_ == 2) {
        if($status == 1) {
          $status_order = 'Оформлен';
        }

        if($status > 1 && $status <= 3) {
          $status_order = 'Готовится';
        }

        if($status >= 4 && $status <= 5) {
          $status_order = 'Ждет в кафе';
        }

        if($status == 6) {
          $status_order = 'Получен';
        }
      }

      $order->this_status_order = $status_order;

      if((int)$order->is_delete == 1){
        $order->this_status_order = 'Удален';
      }

      if((int)$order->is_delete == 1){
        if((int)$order->del_type == 1){
          //кол-центр
          $order->delete_reason .= '. ' . 'Удалил контакт-центр / '. $order->del_name;
        }

        if((int)$order->del_type == 2){
          //сайт
          $order->delete_reason .= '. ' . 'Удалил клиент с сайта';
        }

        if((int)$order->del_type == 3 || (int)$order->del_type == 0){
          //кухня
          $order->delete_reason .= '. ' . 'Удалила кухня';
        }
      }

      if($order->date_time_delete){
        $date_time_delete = explode(' ', $order->date_time_delete);
        $time_delete = $date_time_delete[1];
        $date_delete = $date_time_delete[0];

        $date_delete = (new Controller_concenter)->date_format_new($date_delete);

        $order->date_time_delete = $date_delete . ' ' . $time_delete;
      }

      $date = $order->time_order;
      $date = explode(' ', $date);
      $date_ = (new Controller_concenter)->date_format_new($date[0]);
      $order->time_order = $date_.' '.$date[1];

      if($order->unix_time_to_client != 0 || $order->unix_time_to_client != '0'){
        $pos = strpos($order->unix_time_to_client, '-');

        if ($pos === false) {
          $order->time_to_client = '';
        } else {
          $time1 = explode('-', $order->unix_time_to_client);
          $time2 = (int)$time1[1];
          $time1 = (int)$time1[0];

          if($time1 > 60){
            $time1_h = floor($time1 / 60);
            $time1_m = $time1 - ($time1_h * 60);
            $time1_m = $time1_m == 0 || $time1_m <= 9 ? '0'.$time1_m : $time1_m;

            $time1 = $time1_h.'ч. '.$time1_m.'м.';
          }else{
            $time1 = $time1 > 9 ? $time1 : '0' . $time1;

            $time1 = $time1.'м.';
          }

          if($time2 > 60){
            $time2_h = floor($time2 / 60);
            $time2_m = $time2 - ($time2_h * 60);
            $time2_m = $time2_m == 0 || $time2_m <= 9 ? '0'.$time2_m : $time2_m;
            $time2 = $time2_h.'ч. '.$time2_m.'м.';
          }else{
            $time2 = $time2 > 9 ? $time2 : '0' . $time2;
            $time2 = $time2.'м.';
          }

          $order->time_to_client = $time1.' - '.$time2;
        }

      } else {
        $order->time_to_client = 0;
      }

      $order->text_time = (int)$order->type_order_ == 1 ? 'Доставили: ' : 'Приготовили: ';

      $order->check_pos = Model_site_clients::get_check_pos($base, $order_id);
      $order->check_pos_drive = Model_site_clients::get_check_pos_drive($base, $order_id);

      if($order->check_pos_drive){
        $order->check_pos = -1;
      }

      $order_items_ = Model_site_clients::get_items_order_2($base, $order_id);

      return new GlobalResource([
        'order' => $order,
        'order_items' => $items_order,
        'order_items_' => $order_items_,
        'err_order' => $err_order
      ]);
    }

    public function save_edit_client(Request $request): GlobalResource
    {
      $request->data['mail'] = addslashes($request->data['mail']);
      $res = Model_site_clients::save_data_client($request->data['login'], $request->data['date_bir'], $request->data['mail']);

      if( $res ) {
        Model_site_clients::save_history($request->data['login'], $request->login['id'], date('Y-m-d H:i:s'), 'change_mail', $request->data['mail']);
        Model_site_clients::save_history($request->data['login'], $request->login['id'], date('Y-m-d H:i:s'), 'change_date_bir', $request->data['date_bir']);
      }

      return new GlobalResource([
        'st' => true,
        'text' => 'Успешно сохранено'
      ]);
    }

    public function save_comment(Request $request): GlobalResource
    {
      $my = Helper::getInfoByMy($request->login['login']);
      $request->data['text'] = addslashes($request->data['text']);

      $id = Model_site_clients::insert_new_comment(
        $request->data['number'],
        date('Y-m-d H:i:s'),
        $my->id,
        $request->data['text']
      );

      if($id > 0){
        $client_comments= Model_site_clients::get_client_comments($request->data['number']);

        return new GlobalResource([
          'st' => true,
          'text' => 'Успешно сохранено',
          'client_comments' => $client_comments
        ]);

      } else {
        return new GlobalResource([
          'st' => false,
          'text' => 'Ошибка записи'
        ]);
      }

    }

    public function save_action(Request $request): GlobalResource
    {
      $my = Helper::getInfoByMy($request->login['login']);
      $request->data['description'] = addslashes($request->data['description']);

      $id = Model_site_clients::insert_new_action(
        $request->data['comment_id'],
        $my->id,
        date('Y-m-d H:i:s'),
        $request->data['description'],
        $request->data['raiting'],
        $request->data['type_sale']
      );

      if($id > 0){
        $client_comments = Model_site_clients::get_client_comments($request->data['number']);

        return new GlobalResource([
          'st' => true,
          'text' => 'Успешно сохранено',
          'client_comments' => $client_comments
        ]);

      } else {
        return new GlobalResource([
          'st' => false,
          'text' => 'Ошибка записи'
        ]);
      }

    }

    public function save_promo(Request $request): void
    {
      $my = Helper::getInfoByMy($request->login['login']);

      // даты действия промика
      $start_promo = date('Y-m-d');
      $end_promo = date('Y-m-d', time()+86400*14);

      // размер скидки в %
      $percent = $request->data['percent'];

      $name = array(
        'type' =>'generate',
        'length' => 5
      );

      $dates = array(
        'date_start' => $start_promo,
        'date_end' => $end_promo,
        'time_start' => '00:00',
        'time_end' => '23:59',
        'day_1' => 1,
        'day_2' => 2,
        'day_3' => 3,
        'day_4' => 4,
        'day_5' => 5,
        'day_6' => 6,
        'day_7' => 7
      );

      $city = array(
        'city_id' => 0,
        'point_id' => 0
      );

      $text = array(
        'text_true' => "скидку на всё меню, кроме напитков, соусов, приправ и палочек, в размере $percent%",
        'text_false' => "Промокод действует c $start_promo до $end_promo с 10:00 до 21:30"
      );

      $limit = array(
        'site_only' => 0,
        'free_drive' => 0
      );

      $promo_id = (new Controller_promo)->new_promo($name, 1, 1, 3, '', '', $percent, 2, '', '', 2, '', 0, 0, $dates, 1, $city, $text, $limit, $my->id);

      if((int)$promo_id > 0) {

        $promo_name = Model_site_clients::get_promo_name($promo_id)->name;

        // текст для смс
        $text = "Промокод $promo_name, действует до $end_promo. Заказывай на jacofood.ru!";

        //отправить смс
        (new Controller_sms)->send_sms(
          $request->data['number'],
          $text
        );

        //добавить в ЛК
        $this->send_lk($promo_id, $request->data['number'], $promo_name);
      }

    }

    public function send_lk($promo_id, $user_login, $promo_name): void
    {
      $client = Model_site_clients::get_client($user_login);

      Model_site_clients::insert_user_send_sms_lk(date('Y-m-d H:i:s'),$user_login, $promo_name);

      if($client){
        Model_site_clients::insert_site_users_promo($client->id, $promo_id);
      }
    }

    public function get_code(Request $request): GlobalResource
    {

      $sms_code = $this->generate_code_sms(4);
      $text_sms = "Ваш код $sms_code для подтверждения номера телефона на jacofood.ru";
      $res = Model_site_clients::insert_user_sms_code($request->data['user_id'], $sms_code, date('Y-m-d H:i:s'));

      if((int)$res > 0) {

        Model_site_clients::save_history($request->data['number'], $request->login['id'], date('Y-m-d H:i:s'), 'send_sms', '');

        //отправить смс
        (new Controller_sms)->send_sms(
          $request->data['number'],
          $text_sms
        );

        $client_login_sms= Model_site_clients::get_client_send_sms($request->data['user_id']);

        return new GlobalResource([
          'st' => true,
          'text' => "Код $sms_code направлен клиенту в смс",
          'client_login_sms' => $client_login_sms
        ]);

      } else {

        return new GlobalResource([
          'st' => false,
          'text' => "Ошибка в отправке смс"
        ]);

      }
    }

    public function generate_code_sms($length): string
    {
      $chars = '12456789';
      $numChars = mb_strlen($chars);
      $string = '';

      for ($i = 0; $i < $length; $i++) {
        $string .= mb_substr($chars, rand(1, $numChars) - 1, 1);
      }

      return $string;
    }

    public function export_file_xls(Request $request): BinaryFileResponse
    {
      return Excel::download(new SiteClientsExport($request), 'table_orders.xlsx');
    }

}
