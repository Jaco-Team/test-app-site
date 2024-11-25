<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\Helper;
use App\Models\Model_app_work;
use Illuminate\Http\Request;
use App\Http\Resources\GlobalResource;
use Illuminate\Support\Collection;

class Controller_app_work extends Controller
{
    public function get_all(Request $request): GlobalResource
    {
      return new GlobalResource([
        'module_info' => $request->module_info,
        'items' => Model_app_work::get_app_work_items(),
        'items_min' => Model_app_work::get_app_work_items_min()
      ]);
    }

    public function get_one(Request $request): GlobalResource
    {
      $times_close = Model_app_work::get_times_close($request->data['id']);

      return new GlobalResource([
        'item' => Model_app_work::get_this_item($request->data['id']),
        'apps' => Model_app_work::get_apps(),
        'times_add' => Model_app_work::get_times_add($request->data['id']),
        'times_close' =>  $times_close ? $times_close->time_action : '',
        'cats' => Model_app_work::get_cats(),
      ]);
    }

    public function get_all_for_new(): GlobalResource
    {

      $data = array (
        'name' => '',
        'app_id' => 0,
        'dow' => 0,
        'max_count' => 1,
        'is_active' => 0,
        'time_min' => 0,
        'type_time' => 0,
        'type_new' => 0,
        'description' => '',
        'work_id' => 0
      );

      return new GlobalResource([
        'cats' => Model_app_work::get_cats(),
        'apps' => Model_app_work::get_apps(),
        'item' => $data,
        'times_add' => [],
        'times_close' => ''
      ]);
    }

    public function save_check(Request $request): GlobalResource
    {

      $res = Model_app_work::save_check_item($request->data['type'], $request->data['id'], $request->data['value']);

      return new GlobalResource([
        'st' => $res,
        'text' => $res ? 'Успешно сохранено' : 'Ошибка сохранения'
      ]);
    }

    public function save_new(Request $request): GlobalResource
    {

      if((int)$request->data['work']['type_time'] == 0){
        return new GlobalResource([
          'st' => false,
          'text' => 'Не выбран тип добавления'
        ]);
      }

      if(empty($request->data['work']['dow'])){
        return new GlobalResource([
          'st' => false,
          'text' => 'Необходимо выбрать день недели'
        ]);
      }

      $check = Model_app_work::check_new_item($request->data['work']['app_id'], $request->data['work']['name']);

      if($check){
        return new GlobalResource([
          'st' => false,
          'text' => 'Уборка с таким названием и должностью уже есть'
        ]);
      }

      $time_work = (int)$request->data['work']['time_min'];
      $time_work_sec = $time_work * 60;

      $id = Model_app_work::insert_new_item(
        $request->data['work']['app_id'],
        $request->data['work']['name'],
        $request->data['work']['dow'],
        $request->data['work']['type_new'],
        $time_work,
        $time_work_sec,
        $request->data['work']['description'],
        $request->data['work']['type_time'],
        $request->data['work']['max_count'],
        $request->data['work']['work_id']
      );

      if((int)$id > 0){

        foreach($request->data['times_add'] as $time){
          Model_app_work::insert_times_item($id, $time['time_action'], "1");
        }

        if($request->data['times_close'] != ''){
          Model_app_work::insert_times_item($id, $request->data['times_close'], "2");
        }

        return new GlobalResource([
          'st' => true,
          'text' => 'Успешно сохранено'
        ]);

      } else {

        return new GlobalResource([
          'st' => false,
          'text' => 'Ошибка сохранения'
        ]);

      }

    }

    public function save_edit(Request $request): GlobalResource
    {

      if((int)$request->data['work']['type_time'] == 0){
        return new GlobalResource([
          'st' => false,
          'text' => 'Не выбран тип добавления'
        ]);
      }

      if(empty($request->data['work']['dow'])){
        return new GlobalResource([
          'st' => false,
          'text' => 'Необходимо выбрать день недели'
        ]);
      }

      $time_work = (int)$request->data['work']['time_min'];
      $time_work_sec = $time_work * 60;

      $res = Model_app_work::update_edit_item(
        $request->data['work']['id'],
        $request->data['work']['app_id'],
        $request->data['work']['name'],
        $request->data['work']['dow'],
        $request->data['work']['type_new'],
        $time_work,
        $time_work_sec,
        $request->data['work']['description'],
        $request->data['work']['type_time'],
        $request->data['work']['max_count'],
        $request->data['work']['work_id']
      );

      if($res == 0 || $res > 0){

        Model_app_work::delete_times_item($request->data['work']['id']);

        foreach($request->data['times_add'] as $time){
          Model_app_work::insert_times_item($request->data['work']['id'], $time['time_action'], "1");
        }

        if($request->data['times_close'] != ''){
          Model_app_work::insert_times_item($request->data['work']['id'], $request->data['times_close'], "2");
        }

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
}
