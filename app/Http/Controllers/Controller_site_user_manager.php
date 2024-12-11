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

      if((int)$check->is_graph == 0 && (int)$check->kind < 3){
        $my->show_access = 1;
      } else {
        $my->show_access = 0;
      }

      if((int)$my->point_id == -1){
        array_unshift($points, ['id' => -1, 'name' => 'Все точки']);
      }

      if((int)$my->app_id === 18){
        $apps = Model_site_user_manager::get_specific_apps();
      } else {
        $apps = Model_site_user_manager::get_all_apps($my->kind);
      }

      array_unshift($apps, ['id' => -1, 'name' => 'Все должности', 'is_graph' => 0]);

      return new GlobalResource([
        'module_info' => $request->module_info,
        'apps' => $apps,
        'points' => $points,
        'my' => $my
      ]);
    }

    public function get_users(Request $request): GlobalResource
    {
      $my = Helper::getInfoByMy($request->login['login']);

      $check_app = '';
      $check_name = !empty($request->data['search']) ?
        ' AND (u.name LIKE "%'.$request->data['search'].'%" OR
						u.login LIKE "%'.$request->data['search'].'%")' : ' ';

      if((int)$my->app_id == 18 ){
        $check_app = ' AND app.`id` IN (18, 30, 10, 0) ';
      }

      $users = Model_site_user_manager::get_all_users($my->kind, $request->data['app_id'], $request->data['point_id'], $check_app, $check_name, $my->id);

      return new GlobalResource($users);
    }

    public function get_all_for_new(Request $request): GlobalResource
    {
      $my = Helper::getInfoByMy($request->login['login']);
      $points = Helper::getMyPointList($my->city_id, $my->point_id);
      $cities = Model_site_user_manager::get_cities($my->city_id);

      if((int)$my->app_id === 18){
        $apps = Model_site_user_manager::get_specific_apps();
      } else {
        $apps = Model_site_user_manager::get_all_apps($my->kind);
      }

      if(count($points) > 1){
        array_unshift($points, array('id' => -1, 'name' => 'Все точки', 'city_id' => -1));
      }

      if(count($cities) > 1){
        array_unshift($cities, array('id' => -1, 'name' => 'Все города'));
      }

      $user = [
        'id' => -1,
        'name' => '',
        'auth_code' => '',
        'inn' => '',
        'acc_to_kas' => 0,
        'login' => '',
        'city_id' => '-1',
        'point_id' => '',
        'app_id' => '',
        'img_name' => 'user_0.jpg',
        'img_update' => '',
        'birthday' => '',
        'text_close' => '',
        'fam' => '',
        'otc' => ''
      ];

      return new GlobalResource([
        'appointment' => $apps,
        'point_list' => $points,
        'cities' => $cities,
        'user' => $user
      ]);
    }

    public function get_one_user(Request $request): GlobalResource
    {
      $my = Helper::getInfoByMy($request->login['login']);
      $points = Helper::getMyPointList($my->city_id, $my->point_id);
      $cities = Model_site_user_manager::get_cities($my->city_id);

      if((int)$my->app_id === 18){
        $apps = Model_site_user_manager::get_specific_apps();
      } else {
        $apps = Model_site_user_manager::get_all_apps($my->kind);
      }

      if(count($points) > 1){
        array_unshift($points, array('id' => -1, 'name' => 'Все точки', 'city_id' => -1));
      }

      if(count($cities) > 1){
        array_unshift($cities, array('id' => -1, 'name' => 'Все города'));
      }

      $user = Model_site_user_manager::get_one_user($request->data['user_id']);
      $user->history = Model_site_user_manager::get_history_one_user($request->data['user_id']);

      if(!$user->img_name){
        $user->img_name = 'user_0.jpg';
        $user->date_update = '0';
      }

      $fio 	= explode(" ", $user->name);
      $user->fam  = !empty($fio[0]) ? $fio[0] : '';
      $user->name = !empty($fio[1]) ? $fio[1] : '';
      $user->otc  = !empty($fio[2]) ? $fio[2] : '';

      $phone_history = Model_site_user_manager::get_user_phone_history($request->data['user_id']);
      $users_holidays = Model_site_user_manager::get_user_holidays($request->data['user_id']);

      return new GlobalResource([
        'appointment' => $apps,
        'point_list' => $points,
        'cities' => $cities,
        'user' => $user,
        'phone_history' => $phone_history,
        'users_holidays' => $users_holidays
      ]);
    }

    public function save_new_user(Request $request): GlobalResource
    {
      $my = Helper::getInfoByMy($request->login['login']);

      $request->data['user']['user']['fam'] = Model_site_user_manager::my_mb_up_first(mb_strtolower(ltrim(rtrim($request->data['user']['user']['fam']))));
      $request->data['user']['user']['name'] = Model_site_user_manager::my_mb_up_first(mb_strtolower(ltrim(rtrim($request->data['user']['user']['name']))));
      $request->data['user']['user']['otc'] = Model_site_user_manager::my_mb_up_first(mb_strtolower(ltrim(rtrim($request->data['user']['user']['otc']))));

      $request->data['user']['user']['fam'] = str_replace( array( '\'', '"', ',', '.', '!', '?', ';', '<', '>', '-', '@', '#', '$' ), '', $request->data['user']['user']['fam']);
      $request->data['user']['user']['name'] = str_replace( array( '\'', '"', ',', '.', '!', '?', ';', '<', '>', '-', '@', '#', '$' ), '', $request->data['user']['user']['name']);
      $request->data['user']['user']['otc'] = str_replace( array( '\'', '"', ',', '.', '!', '?', ';', '<', '>', '-', '@', '#', '$' ), '', $request->data['user']['user']['otc']);

      if(strlen($request->data['user']['user']['fam']) == 0){
        return new GlobalResource([
          'st' => false,
          'text' => 'Поле Фамилия не заполнено'
        ]);
      }

      if(strlen($request->data['user']['user']['name']) == 0){
        return new GlobalResource([
          'st' => false,
          'text' => 'Поле Имя не заполнено'
        ]);
      }

      if(strlen($request->data['user']['user']['otc']) == 0){
        return new GlobalResource([
          'st' => false,
          'text' => 'Поле Отчество не заполнено'
        ]);
      }

      $short_name = $request->data['user']['user']['fam'].' '.mb_substr($request->data['user']['user']['name'], 0, 1).'. '.mb_substr($request->data['user']['user']['otc'], 0, 1).'.';
      $full_name = $request->data['user']['user']['fam'].' '.$request->data['user']['user']['name'].' '.$request->data['user']['user']['otc'];

      $request->data['user']['user']['login'] = ltrim( rtrim($request->data['user']['user']['login']) );

      $request->data['user']['user']['login'] = str_replace("+", "", $request->data['user']['user']['login']);
      $request->data['user']['user']['login'] = str_replace("_", "", $request->data['user']['user']['login']);
      $request->data['user']['user']['login']= str_replace("-", "", $request->data['user']['user']['login']);
      $request->data['user']['user']['login'] = str_replace(" ", "", $request->data['user']['user']['login']);

      if( strlen($request->data['user']['user']['login']) == 0 ){
        return new GlobalResource([
          'st' => false,
          'text' => 'Поле Номер телефона не заполнено'
        ]);
      }

      $check_user_login = Model_site_user_manager::check_user_login($request->data['user']['user']['login']);

      if($check_user_login){
        return new GlobalResource([
          'st' => false,
          'text' => 'Номер телефона уже есть в системе'
        ]);
      }

      if(empty($request->data['user']['user']['birthday']) || strlen($request->data['user']['user']['birthday']) === 0){
        return new GlobalResource([
          'st' => false,
          'text' => 'Введите дату рождения'
        ]);
      }

      $request->data['user']['user']['auth_code'] = ltrim(rtrim($request->data['user']['user']['auth_code']));

      $request->data['user']['user']['auth_code'] = str_replace("+", "", $request->data['user']['user']['auth_code']);
      $request->data['user']['user']['auth_code'] = str_replace("_", "", $request->data['user']['user']['auth_code']);
      $request->data['user']['user']['auth_code'] = str_replace("-", "", $request->data['user']['user']['auth_code']);
      $request->data['user']['user']['auth_code'] = str_replace(" ", "", $request->data['user']['user']['auth_code']);

      if(strlen($request->data['user']['user']['auth_code']) != 0 ){

        if( strlen($request->data['user']['user']['auth_code']) != 4 ){
          return new GlobalResource([
            'st' => false,
            'text' => 'Код авторизации должен состоять из 4 символов'
          ]);
        }

        $check_code = Model_site_user_manager::check_user_auth_code($request->data['user']['user']['auth_code']);

        if($check_code){
          return new GlobalResource([
            'st' => false,
            'text' => 'Данный код авторизации не доступен'
          ]);
        }
      }

      if((int)$request->data['user']['user']['app_id'] == 5 || (int)$request->data['user']['user']['app_id'] == 6 || (int)$request->data['user']['user']['app_id'] == 12 ){
        if(strlen($request->data['user']['user']['auth_code']) == 0){
          return new GlobalResource([
            'st' => false,
            'text' => 'Поле код авторизации не заполнено'
          ]);
        }
      }

      if(strlen($request->data['user']['user']['inn']) > 0 && strlen($request->data['user']['user']['inn']) != 12 ){
        return new GlobalResource([
          'st' => false,
          'text' => 'Поле ИНН не заполнено '.strlen($request->data['user']['user']['inn'])
        ]);
      }

      if(empty($request->data['user']['user']['app_id'])){
        return new GlobalResource([
          'st' => false,
          'text' => 'Выберите должность'
        ]);
      }

      if(empty($request->data['user']['user']['city_id'])){
        return new GlobalResource([
          'st' => false,
          'text' => 'Выберите город'
        ]);
      }

      if(empty($request->data['user']['user']['point_id'])){
        return new GlobalResource([
          'st' => false,
          'text' => 'Выберите точку'
        ]);
      }

      if( (int)$request->data['user']['user']['city_id'] == -1 && (int)$request->data['user']['user']['point_id'] > 0 ){
        return new GlobalResource([
          'st' => false,
          'text' => 'Выберите конкретный город'
        ]);
      }

      $user_id = Model_site_user_manager::insert_new_user($full_name, $short_name, $request->data['user']['user']['auth_code'], $request->data['user']['user']['inn'], $request->data['user']['user']['acc_to_kas'], $request->data['user']['user']['birthday'], $request->data['user']['user']['login'], date('Y-m-d'), $request->data['user']['user']['name'], $request->data['user']['user']['fam'], $request->data['user']['user']['otc']);

      if($user_id == 0 || !$user_id){
        return new GlobalResource([
          'st' => false,
          'text' => 'Ошибка записи'
        ]);
      }

      Model_site_user_manager::insert_user_history($user_id, $full_name, $short_name, $request->data['user']['user']['login'], date('Y-m-d H:i:s'), $request->data['user']['user']['birthday'], $request->data['user']['user']['auth_code'],
        $request->data['user']['user']['acc_to_kas'], "1", $request->data['user']['user']['inn'], $request->data['user']['user']['app_id'], $request->data['user']['user']['point_id'], $request->data['user']['user']['city_id'], $my->id, "");

      Model_site_user_manager::insert_user_privileges($request->data['user']['user']['app_id'], $request->data['user']['user']['point_id'], $request->data['user']['user']['city_id'], $user_id);

      $res = (new Controller_sms)->send_sms(
        $request->data['user']['user']['login'],
        'Ты зарегистрирован в системе ШЕФ. Пожалуйста, подтверди свой номер телефона, перейдя по ссылке: http://jacosoft-dop.ru/registration'
      );

      return new GlobalResource([
        'st' => true,
        'sms' => $res
      ]);

    }

    public function save_edit_user(Request $request): GlobalResource
    {
      $my = Helper::getInfoByMy($request->login['login']);

      $request->data['user']['user']['fam'] = Model_site_user_manager::my_mb_up_first(mb_strtolower(ltrim(rtrim($request->data['user']['user']['fam']))));
      $request->data['user']['user']['name'] = Model_site_user_manager::my_mb_up_first(mb_strtolower(ltrim(rtrim($request->data['user']['user']['name']))));
      $request->data['user']['user']['otc'] = Model_site_user_manager::my_mb_up_first(mb_strtolower(ltrim(rtrim($request->data['user']['user']['otc']))));

      $request->data['user']['user']['fam'] = str_replace( array( '\'', '"', ',', '.', '!', '?', ';', '<', '>', '-', '@', '#', '$' ), '', $request->data['user']['user']['fam']);
      $request->data['user']['user']['name'] = str_replace( array( '\'', '"', ',', '.', '!', '?', ';', '<', '>', '-', '@', '#', '$' ), '', $request->data['user']['user']['name']);
      $request->data['user']['user']['otc'] = str_replace( array( '\'', '"', ',', '.', '!', '?', ';', '<', '>', '-', '@', '#', '$' ), '', $request->data['user']['user']['otc']);

      if(strlen($request->data['user']['user']['fam']) == 0){
        return new GlobalResource([
          'st' => false,
          'text' => 'Поле Фамилия не заполнено'
        ]);
      }

      if(strlen($request->data['user']['user']['name']) == 0){
        return new GlobalResource([
          'st' => false,
          'text' => 'Поле Имя не заполнено'
        ]);
      }

      if(strlen($request->data['user']['user']['otc']) == 0){
        return new GlobalResource([
          'st' => false,
          'text' => 'Поле Отчество не заполнено'
        ]);
      }

      $short_name = $request->data['user']['user']['fam'].' '.mb_substr($request->data['user']['user']['name'], 0, 1).'. '.mb_substr($request->data['user']['user']['otc'], 0, 1).'.';
      $full_name = $request->data['user']['user']['fam'].' '.$request->data['user']['user']['name'].' '.$request->data['user']['user']['otc'];

      if(empty($request->data['user']['user']['birthday']) || strlen($request->data['user']['user']['birthday']) === 0){
        return new GlobalResource([
          'st' => false,
          'text' => 'Введите дату рождения'
        ]);
      }

      if((int)$request->data['user']['user']['app_id'] == 5 || (int)$request->data['user']['user']['app_id'] == 6 || (int)$request->data['user']['user']['app_id'] == 12 ){
        if(strlen($request->data['user']['user']['auth_code']) == 0){
          return new GlobalResource([
            'st' => false,
            'text' => 'Поле код авторизации не заполнено'
          ]);
        }
      }

      if(strlen($request->data['user']['user']['inn']) > 0 && strlen($request->data['user']['user']['inn']) != 12 ){
        return new GlobalResource([
          'st' => false,
          'text' => 'Поле ИНН не заполнено '.strlen($request->data['user']['user']['inn'])
        ]);
      }

      if(empty($request->data['user']['user']['city_id'])){
        return new GlobalResource([
          'st' => false,
          'text' => 'Выберите город'
        ]);
      }

      if(empty($request->data['user']['user']['point_id'])){
        return new GlobalResource([
          'st' => false,
          'text' => 'Выберите точку'
        ]);
      }

      if( (int)$request->data['user']['user']['city_id'] == -1 && (int)$request->data['user']['user']['point_id'] > 0 ){
        return new GlobalResource([
          'st' => false,
          'text' => 'Выберите конкретный город'
        ]);
      }

      $check_user = Model_site_user_manager::check_user($request->data['user']['user']['id']);
      $user_app = Model_site_user_manager::get_user_app($request->data['user']['user']['id']);

      if((int)$check_user->auth_code != (int)$request->data['user']['user']['auth_code']){

        $request->data['user']['user']['auth_code'] = ltrim(rtrim($request->data['user']['user']['auth_code']));

        $request->data['user']['user']['auth_code'] = str_replace("+", "", $request->data['user']['user']['auth_code']);
        $request->data['user']['user']['auth_code'] = str_replace("_", "", $request->data['user']['user']['auth_code']);
        $request->data['user']['user']['auth_code'] = str_replace("-", "", $request->data['user']['user']['auth_code']);
        $request->data['user']['user']['auth_code'] = str_replace(" ", "", $request->data['user']['user']['auth_code']);

        if(strlen($request->data['user']['user']['auth_code']) != 4){
          return new GlobalResource([
            'st' => false,
            'text' => 'Код авторизации должен состоять из 4 символов'
          ]);
        }

        $check_code = Model_site_user_manager::check_user_auth_code($request->data['user']['user']['auth_code']);

        if(!empty($check_code)){
          return new GlobalResource([
            'st' => false,
            'text' => 'Данный код авторизации не доступен'
          ]);
        }
      }

      if((int)$check_user->login != (int)$request->data['user']['user']['login']){
        $check_code = Model_site_user_manager::check_user_login($request->data['user']['user']['login']);

        $check_code = (array)$check_code;

        if(array_key_exists('id', $check_code) ){
          return new GlobalResource([
            'st' => false,
            'text' => 'Новый номер телефона уже используется'
          ]);
        }

        Model_site_user_manager::insert_user_phone_history($request->data['user']['user']['id'], date('Y-m-d'), $check_user->login, $request->data['user']['user']['login']);

        Model_site_user_manager::update_user_login($request->data['user']['user']['login'], $request->data['user']['user']['id']);

      }

      $request->data['textDel'] = addslashes($request->data['textDel']);

      // Причина увольнения
      if((int)$request->data['user']['user']['app_id'] == 0 && (int)$check_user->app_id != 0 ){

        if($request->data['textDel'] != ""){

          Model_site_user_manager::update_user_date_del(date('Y-m-d'), $request->data['user']['user']['id']);

          Model_site_user_manager::insert_user_del_history($my->id, date('Y-m-d H:i:s'), $request->data['textDel'], $request->data['user']['user']['id'], $request->data['user']['user']['login']);

        }

      }

      Model_site_user_manager::update_user_data(
        $full_name,
        $short_name,
        $request->data['user']['user']['name'],
        $request->data['user']['user']['fam'],
        $request->data['user']['user']['otc'],
        $request->data['user']['user']['auth_code'],
        $request->data['user']['user']['inn'],
        $request->data['user']['user']['acc_to_kas'],
        $request->data['user']['user']['birthday'],
        $request->data['user']['user']['login'],
        $request->data['user']['user']['id']
      );

      Model_site_user_manager::insert_user_history(
        $request->data['user']['user']['id'],
        $full_name, $short_name,
        $request->data['user']['user']['login'],
        date('Y-m-d H:i:s'),
        $request->data['user']['user']['birthday'],
        $request->data['user']['user']['auth_code'],
        $request->data['user']['user']['acc_to_kas'],
        "1",
        $request->data['user']['user']['inn'],
        $request->data['user']['user']['app_id'],
        $request->data['user']['user']['point_id'],
        $request->data['user']['user']['city_id'],
        $my->id,
        $request->data['textDel']
      );

      Model_site_user_manager::update_user_privileges(
        $request->data['user']['user']['app_id'],
        $request->data['user']['user']['point_id'],
        $request->data['user']['user']['city_id'],
        $request->data['user']['user']['id']
      );

      if((int)$request->data['graphType'] == 1 || (int)$request->data['graphType'] == 2){
        $this->update_graph(
          $request->data['graphType'],
          $request->data['user']['user']['app_id'],
          $request->data['user']['user']['point_id'],
          $request->data['user']['user']['city_id'],
          $request->data['user']['user']['id'],
          $user_app->appointment_id
        );
      }

      return new GlobalResource([
        'st' => true,
        'text' => 'Успешно сохранено'
      ]);

    }

    public function update_graph($GraphType, $app_id, $point_id, $city_id, $user_id, $old_app_id): void
    {
      if((int)$GraphType == 1) {
        //с текущего

        Model_site_user_manager::update_user_privileges(
          $app_id,
          $point_id,
          $city_id,
          $user_id
        );

        $date = explode('-', date('Y-m-d'));
        $min_date = $date;
        $this_d = (int)$date[2];
        $this_date = $date[0] . '-' . $date[1] . '-';

        if ($this_d >= 1 && $this_d < 16) {
          $this_date .= '01';
          $max_day = $min_date[0] . '-' . $min_date[1] . '-15';
        } else {
          $this_date .= '16';
          $max_day = date("Y-m-t", strtotime($min_date[0] . '-' . $min_date[1] . '-' . $min_date[2]));
        }

        $check_user_smena = Model_site_user_manager::check_user_smena(
          $old_app_id,
          $user_id,
          $this_date
        );

        if (!$check_user_smena) {
          $check_user_smena = 0;
        } else {
          $check_user_smena = $check_user_smena->smena_id;
        }

        //удаляем все записи со старой должности
        Model_site_user_manager::update_user_app_data(
          $check_user_smena,
          $old_app_id,
          $user_id,
          $max_day
        );

        Model_site_user_manager::insert_user_cafe_smena_event(
          date('Y-m-d H:i:s'),
          $check_user_smena,
          $user_id
        );

        Model_site_user_manager::update_user_cafe_smena_days(
          $app_id,
          $old_app_id,
          $user_id,
          date('Y-m-d')
        );

        Model_site_user_manager::update_user_cafe_smena_hours(
          $app_id,
          $old_app_id,
          $user_id,
          date('Y-m-d')
        );

        $check = Model_site_user_manager::check_user_smena_info(
          $this_date,
          $check_user_smena,
          $user_id,
          $app_id
        );

        if (!$check) {

          //повар универсал и стажер в повара
          Model_site_user_manager::update_povar_cafe_smena_days(
            $user_id,
            $this_date,
            $max_day
          );

          //стажер в кассира
          Model_site_user_manager::update_kassir_cafe_smena_days(
            $user_id,
            $this_date,
            $max_day
          );

          //стажер в куха
          Model_site_user_manager::update_intern_cafe_smena_days(
            $user_id,
            $this_date,
            $max_day
          );
        }

      }

      if((int)$GraphType == 2) {
        //со следующего

        $date = explode('-', date('Y-m-d'));
        $min_date = $date;
        $this_d = (int)$date[2];
        $this_date = $date[0] . '-' . $date[1] . '-';

        $next_per = '';

        if ($this_d >= 1 && $this_d < 16) {
          $this_date .= '01';
          $max_day = $min_date[0] . '-' . $min_date[1] . '-15';

          $next_per = $min_date[0] . '-' . $min_date[1] . '-16';
        } else {
          $this_date .= '16';
          $max_day = date("Y-m-t", strtotime($min_date[0] . '-' . $min_date[1] . '-' . $min_date[2]));

          $min_date[1] = (int)$min_date[1] + 1;

          if ($min_date[1] > 12) {
            (int)$min_date[1] = '01';
            (int)$min_date[0]++;
          }

          $min_date[1] = (int)$min_date[1] > 9 ? $min_date[1] : '0' . $min_date[1];
          $next_per = $min_date[0] . '-' . $min_date[1] . '-01';
        }

        Model_site_user_manager::update_user_data_change(
          $next_per,
          $app_id,
          $point_id,
          $city_id,
          $user_id
        );

        $check_user_smena = Model_site_user_manager::check_user_smena(
          $old_app_id,
          $user_id,
          $this_date
        );

        if (!$check_user_smena) {
          $check_user_smena = 0;
        } else {
          $check_user_smena = $check_user_smena->smena_id;
        }

        $check_next_per = Model_site_user_manager::check_next_per(
          $old_app_id,
          $user_id,
          $max_day
        );

        if (count($check_next_per) > 0) {

          //удаляем все записи со старой должности
          Model_site_user_manager::update_user_old_app(
            $app_id,
            $old_app_id,
            $user_id,
            $max_day
          );

          Model_site_user_manager::insert_user_cafe_smena_event(
            date('Y-m-d H:i:s'),
            $check_user_smena,
            $user_id
          );

          Model_site_user_manager::update_user_cafe_smena_days_2(
            $app_id,
            $old_app_id,
            $user_id,
            $max_day
          );

          Model_site_user_manager::update_user_cafe_smena_hours(
            $app_id,
            $old_app_id,
            $user_id,
            $max_day
          );

          //повар универсал и стажер в повара
          Model_site_user_manager::update_povar_cafe_smena_days(
            $user_id,
            $this_date,
            $max_day
          );

          //стажер в кассира
          Model_site_user_manager::update_kassir_cafe_smena_days(
            $user_id,
            $this_date,
            $max_day
          );

          //стажер в куха
          Model_site_user_manager::update_intern_cafe_smena_days(
            $user_id,
            $this_date,
            $max_day
          );

        }

      }
    }
}
