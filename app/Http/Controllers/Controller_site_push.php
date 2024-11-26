<?php

namespace App\Http\Controllers;

use App\Http\Resources\GlobalResource;
use App\Models\Model_site_push;
use Illuminate\Http\Request;

class Controller_site_push extends Controller
{
    public function get_all(Request $request): GlobalResource
    {
      $cities = Model_site_push::get_cities();
      $active =  Model_site_push::get_push_active();
      $non_active = Model_site_push::get_push_none_active();

      if(count($active) > 0){
        $active =  Model_site_push::get_city_name_push($active);
      }

      if(count($non_active) > 0){
        $non_active =  Model_site_push::get_city_name_push($non_active);
      }

      $push = [
        'active' => $active,
        'non_active' => $non_active,
      ];

      return new GlobalResource([
        'module_info' => $request->module_info,
        'cities' => $cities,
        'push' => $push
      ]);
    }

    public function get_all_for_new(Request $request): GlobalResource
    {
      $cities = Model_site_push::get_cities();
      $items = Model_site_push::get_all_items();
      $banners = Model_site_push::get_banners();

      return new GlobalResource([
        'cities' => $cities,
        'items' => $items,
        'banners' => $banners
      ]);
    }

    public function get_one(Request $request): GlobalResource
    {
      $cities = Model_site_push::get_cities();
      $items = Model_site_push::get_all_items();
      $banners = Model_site_push::get_banners();
      $res_city = Model_site_push::get_city($request->data['push_id']);
      $res_all_city = Model_site_push::get_all_city($request->data['push_id']);
      $this_push = Model_site_push::get_this_push($request->data['push_id']);

      $this_push->city_id = count($res_all_city) > 0 ? $res_all_city :  $res_city;

      return new GlobalResource([
        'cities' => $cities,
        'items' => $items,
        'banners' => $banners,
        'this_push' => $this_push
      ]);
    }

    public function save_active(Request $request): GlobalResource
    {

      $res = Model_site_push::save_check_push($request->data['id'], $request->data['is_active']);

      return new GlobalResource([
        'st' => $res,
        'text' => $res ? 'Успешно сохранено' : 'Ошибка сохранения'
      ]);
    }

    public function save_new(Request $request): GlobalResource
    {

      if(empty($request->data['is_active'])){
        $request->data['is_active'] = 0;
      }

      if(empty($request->data['is_send'])){
        $request->data['is_send'] = 0;
      }

      if(empty($request->data['is_auth'])){
        $request->data['is_auth'] = 0;
      }

      $request->data['name'] = addslashes($request->data['name']);
      $request->data['title'] = addslashes($request->data['title']);
      $request->data['text'] = addslashes($request->data['text']);

      $id = Model_site_push::insert_new_push(
        $request->data['name'],
        $request->data['date_start'],
        $request->data['time_start'],
        $request->data['is_send'],
        $request->data['is_auth'],
        $request->data['title'],
        $request->data['text'],
        $request->data['type'],
        $request->data['is_active'],
        $request->data['item_id'],
        $request->data['ban_id'],
        date('Y_m_d_H_i_s')
      );

      if(count($request->data['city_id']) > 0 && (int)$id > 0){

        foreach($request->data['city_id'] as $city){
          Model_site_push::insert_push_cities($id, $city['id']);
        }

      }

      return new GlobalResource([
        'st' => (int)$id > 0,
        'text' => (int)$id > 0 ? 'Успешно сохранено' : 'Ошибка сохранения'
      ]);

    }

    public function save_edit(Request $request): GlobalResource
    {

      if(empty($request->data['is_active'])){
        $request->data['is_active'] = 0;
      }

      if(empty($request->data['is_send'])){
        $request->data['is_send'] = 0;
      }

      if(empty($request->data['is_auth'])){
        $request->data['is_auth'] = 0;
      }

      $request->data['name'] = addslashes($request->data['name']);
      $request->data['title'] = addslashes($request->data['title']);
      $request->data['text'] = addslashes($request->data['text']);

      $res = Model_site_push::update_edit_push(
        $request->data['name'],
        $request->data['date_start'],
        $request->data['time_start'],
        $request->data['is_send'],
        $request->data['is_auth'],
        $request->data['title'],
        $request->data['text'],
        $request->data['type'],
        $request->data['is_active'],
        $request->data['item_id'],
        $request->data['ban_id'],
        date('Y_m_d_H_i_s'),
        $request->data['id']
      );

      if($res == 0 || $res > 0){

        Model_site_push::delete_push_cities($request->data['id']);

        if(count($request->data['city_id']) > 0){

          foreach($request->data['city_id'] as $city){
            Model_site_push::insert_push_cities($request->data['id'], $city['id']);
          }

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
