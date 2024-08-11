<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\Helper;
use App\Models\Model_concenter;
use Illuminate\Http\Request;
use App\Http\Resources\GlobalResource;
use Illuminate\Support\Collection;

class Controller_concenter extends Controller
{
    public function get_all(Request $request): GlobalResource
    {
        return new GlobalResource([
            'module_info' => $request->module_info,
            'cities' => Model_concenter::get_all_cities(),
            'points' => Model_concenter::get_all_points()
        ]);
    }

    public function sec_to_time(int $seconds)
    {
        if( $seconds < 0 ) {
            $seconds = $seconds * -1;
        }

        $minutes = floor($seconds / 60); // Считаем минуты
        $hours = floor($minutes / 60); // Считаем количество полных часов
        $minutes = $minutes - ($hours * 60);  // Считаем количество оставшихся минут

        $hours = (int)$hours > 9 ? $hours : '0'.$hours;
        $minutes = (int)$minutes > 9 ? $minutes : '0'.$minutes;

        return $hours.':'.$minutes;
    }

    public function date_format_new($date){
        $date = explode('-', $date);

        switch((int)$date[1]){
            case 1:{
                $m = 'Января';
                break;}
            case 2:{
                $m = 'Февраля';
                break;}
            case 3:{
                $m = 'Марта';
                break;}
            case 4:{
                $m = 'Апреля';
                break;}
            case 5:{
                $m = 'Мая';
                break;}
            case 6:{
                $m = 'Июня';
                break;}
            case 7:{
                $m = 'Июля';
                break;}
            case 8:{
                $m = 'Августа';
                break;}
            case 9:{
                $m = 'Сентября';
                break;}
            case 10:{
                $m = 'Октября';
                break;}
            case 11:{
                $m = 'Ноября';
                break;}
            case 12:{
                $m = 'Декабря';
                break;}
        }

        return $date[2].' '.$m.' '.$date[0];
    }

    public function get_orders(Request $request): GlobalResource
    {
        $base = Helper::get_base($request->data['point_id']);

        $orders = Model_concenter::get_orders_new($request->data['point_id'], $base, $request->data['date']);

        $orders = Collection::make($orders);

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
                        $order->to_time = $this->sec_to_time( $order->to_time_sec );
                    }else{
                        $order->to_time = '';
                    }

                    return $order;
                });
            },
        ]);

        return new GlobalResource([
            'orders' => $orders ?? []
        ]);
    }

    public function get_order_new(Request $request): GlobalResource
    {
        $base = Helper::get_base($request->data['point_id']);

        $city = Model_concenter::get_city_by_point($request->data['point_id']);

        $order_info = Model_concenter::get_order_info(
            $request->data['point_id'],
            $request->data['order_id'],
            $city->name,
            $base
        );

        if( (int)$order_info->is_preorder == 1 ){

            $time = explode(' ', $order_info->date_time_preorder_or)[1];
            $time = explode(':', $time);


            $order_info->date_time_pred = [
                'date' => explode(' ', $order_info->date_time_preorder_or)[0],
                'time' => $time[0].':'.$time[1]
            ];
        }


        $status = (int)$order_info->status_order;
        $status_order = '';

        if((int)$order_info->type_order_ == 1){
            if($status == 1){ $status_order = 'Оформлен'; }else{ if($status < 1){ $type1 = 2; }else{ $type1 = 0; } }
            if($status > 1 && $status <= 4){ $status_order = 'Готовится'; }else{ if($status < 2){ $type2 = 2; }else{ $type2 = 0; } }
            if($status == 5){ $status_order = 'У курьера'; }else{ if($status < 5){ $type3 = 2; }else{ $type3 = 0; } }
            if($status == 6){ $status_order = 'Доставлен'; }else{ if($status < 6){ $type4 = 2; }else{ $type4 = 0; } }
        }

        if((int)$order_info->type_order_ == 2){
            if($status == 1){ $status_order = 'Оформлен'; }else{ if($status < 1){ $type1 = 2; }else{ $type1 = 0; } }
            if($status > 1 && $status <= 3){ $status_order = 'Готовится'; }else{ if($status < 2){ $type2 = 2; }else{ $type2 = 0; } }
            if($status >= 4 && $status <= 5){ $status_order = 'Ждет в кафе'; }else{ if($status < 5){ $type3 = 2; }else{ $type3 = 0; } }
            if($status == 6){ $status_order = 'Получен'; }else{ if($status < 6){ $type4 = 2; }else{ $type4 = 0; } }
        }

        $order_info->this_status_order = $status_order;

        if((int)$order_info->is_delete == 1){
            $order_info->this_status_order = 'Удален';
        }

        if((int)$order_info->is_delete == 1){
            if((int)$order_info->del_type == 1){
                //кол-центр
                $order_info->delete_reason .= '. ' . 'Удалил контакт-центр / '. $order_info->del_name;
            }

            if((int)$order_info->del_type == 2){
                //сайт
                $order_info->delete_reason .= '. ' . 'Удалил клиент с сайта';
            }

            if((int)$order_info->del_type == 3 || (int)$order_info->del_type == 0){
                //кухня
                $order_info->delete_reason .= '. ' . 'Удалила кухня';
            }
        }

        if( $order_info->date_time_delete ){
            $date_time_delete = explode(' ', $order_info->date_time_delete);
            $time_delete = $date_time_delete[1];
            $date_delete = $date_time_delete[0];

            $date_delete = $this->date_format_new($date_delete);

            $order_info->date_time_delete = $date_delete . ' ' . $time_delete;
        }

        $date = $order_info->time_order;
        $date = explode(' ', $date);
        $date_ = $this->date_format_new($date[0]);
        $order_info->time_order = $date_.' '.$date[1];

        if( $order_info->unix_time_to_client != 0 || $order_info->unix_time_to_client != '0' ){
            $pos = strpos($order_info->unix_time_to_client, '-');

            if ($pos === false) {
                $order_info->time_to_client = '';
            } else {
                $time1 = explode('-', $order_info->unix_time_to_client);
                $time2 = (int)$time1[1];
                $time1 = (int)$time1[0];

                if($time1 > 60){
                    $time1_h = floor($time1 / 60);
                    $time1_m = $time1 - ($time1_h * 60);
                    $time1_m = $time1_m == 0 || $time1_m <= 9 ? '0'.$time1_m : $time1_m;

                    $time1 = $time1_h.'ч. '.$time1_m.'м.';
                }else{
                    $time1 = (int)$time1 > 9 ? $time1 : '0' . $time1;

                    $time1 = $time1.'м.';
                }

                if($time2 > 60){
                    $time2_h = floor($time2 / 60);
                    $time2_m = $time2 - ($time2_h * 60);
                    $time2_m = $time2_m == 0 || $time2_m <= 9 ? '0'.$time2_m : $time2_m;

                    $time2 = $time2_h.'ч. '.$time2_m.'м.';
                }else{
                    $time2 = (int)$time2 > 9 ? $time2 : '0' . $time2;

                    $time2 = $time2.'м.';
                }

                $order_info->time_to_client = $time1.' - '.$time2;
            }
        }else{
            $order_info->time_to_client = 0;
        }

        $order_info->text_time = (int)$order_info->type_order_ == 1 ? 'Заказ привезут через: ' : 'Заказ можно забрать через: ';

        $order_info->check_pos = Model_concenter::get_driver_close_dist_other($base, $request->data['order_id']);
        $order_info->check_pos_drive = Model_concenter::get_driver_close_dist($base, $request->data['order_id']);

        if( $order_info->check_pos_drive ){
            $order_info->check_pos = -1;
        }

        $order_items = Model_concenter::get_order_items($request->data['order_id'], $base);

        if( (int)$order_info->type_order_ == 1 ){
            $order_items[] = [
                'id' => -1,
                'name' => 'Доставка',
                'item_id' => -1,
                'count' => 1,
                'price' => $order_info->sum_div
            ];
        }

        return new GlobalResource([
            'order' => $order_info,
            'order_items' => $order_items,
            'order_items_' => Model_concenter::get_order_items_ready($request->data['order_id'], $base)
        ]);
    }

    public function fake_user(Request $request): GlobalResource
    {
        $base = Helper::get_base($request->data['point_id']);

        $order_info = Model_concenter::get_order_info_min($base, $request->data['order_id']);

        if( (int)$order_info->status_order != 5 ){
            return new GlobalResource([
                'st' => false,
                'text' => 'Заказ должен быть в статусе "В пути"'
            ]);
        }

        if( (int)$order_info->status_order == 6 ){
            return new GlobalResource([
                'st' => false,
                'text' => 'Заказ уже у клиента'
            ]);
        }

        $dop_info = Model_concenter::get_driver_close_dist($base, $request->data['order_id']);

        if( $dop_info ){
            return new GlobalResource([
                'st' => false,
                'text' => 'К заказу применим только 1 комментарий'
            ]);
        }

        $request->data['text'] = addslashes($request->data['text']);

        if( (int)$order_info->summ_div_driver == 0 ){
            return new GlobalResource([
                'st' => false,
                'text' => 'При сохранении произошла ошибка, попробуй еще раз'
            ]);
        }

        Model_concenter::plus_time_pred($base, $request->data['order_id']);

        if( strlen($order_info->unix_time_to_client) > 3 ){

            $pieces = explode("-", $order_info->unix_time_to_client);

            $pieces[1] = (int)$pieces[1] + 30;

            $pieces = $pieces[0].'-'.$pieces[1];

            Model_concenter::update_time_to_client($base, $request->data['order_id'], $pieces);
        }

        $id = Model_concenter::insert_order_driver_cash_other(
            $base,
            $order_info->driver_id,
            $request->login->id,
            $request->data['order_id'],
            $order_info->summ_div_driver,
            $request->data['text']
        );

        return new GlobalResource([
            'st' => $id > 0 ? true : false,
            'text' => $id > 0 ? 'Успешно' : 'Ошибка при сохранении',
        ]);
    }

    public function close_order_center(Request $request): GlobalResource
    {
        $base = Helper::get_base($request->data['point_id']);

        $order_info = Model_concenter::get_order_info_min($base, $request->data['order_id']);

        $request->data['ans'] = addslashes($request->data['ans']);

        if( (int)$order_info->type_order == 3 || (int)$order_info->type_order == 4 ){
            return new GlobalResource([
                'st' => false,
                'text' => 'Можно удалить только доставку или самовывоз'
            ]);
        }

        if((int)$order_info->is_delete == 1){
            return new GlobalResource([
                'st' => false,
                'text' => 'Заказ уже удален'
            ]);
        }

        if( $request->data['ans'] == ""){
            return new GlobalResource([
                'st' => false,
                'text' => 'Не указана причина :)'
            ]);
        }

        if((int)$order_info->online_pay == 1 && (int)$order_info->status_order != 6){

            $user_info = Model_concenter::get_user_login_info($request->login['id']);

            $url = "https://jacochef.ru/api/v1/close_order_ret_pay.php";

            $post_data = array (
                'type'   	=> 'order_to_return',
                'order_id' 	=> $request->data['order_id'],
                'point_id'	=> $request->data['point_id'],
                'user_phone'=> $order_info->number,
                'user_type'	=> '1',
                'my_login'	=> $user_info->login,
                'my_pwd'	=> $user_info->pwd
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

            $response = json_decode($output, JSON_UNESCAPED_UNICODE);

            if((int)$response['type'] == 1){
                return new GlobalResource([
                    'st' => false,
                    'text' => $response['text'],
                    'res' => $response
                ]);
            }
        }

        if((int)$order_info->status_order != 6){

            if( $order_info->promo_id && (int)$order_info->promo_id > 0 ) {
                Model_concenter::return_active_promo($order_info->promo_id);
            }

            $res = Model_concenter::update_del_order(
                $base,
                $request->data['order_id'],
                $request->login['id'],
                $request->data['ans']
            );

            return new GlobalResource([
                'st' => $res,
                'text' => !$res ? 'Ошибка удаления' : ''
            ]);
        }else{
            return new GlobalResource([
                'st' => false,
                'text' => 'Заказ уже выполнен'
            ]);
        }
    }
}
