<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\Helper;
use App\Http\Resources\GlobalResource;
use App\Models\Model_sklad_items_module;
use Illuminate\Http\Request;

class Controller_sklad_items_module extends Controller
{
  public function get_all(Request $request): GlobalResource
  {

    $all_items = Model_sklad_items_module::get_all_items();
    $all_items_free = Model_sklad_items_module::get_all_items_free();
    $main_cat = Model_sklad_items_module::get_main_cats();

    foreach ($main_cat as $cat) {
      $cats = Model_sklad_items_module::get_item_cats($cat->id);

      foreach ($cats as $cat_) {
        $check = false;

        foreach ($all_items as $item) {
          if ((int)$item->cat_id == (int)$cat_->id) {
            $check = true;
            $cat_->items[] = $item;
          }
        }

        if (!$check) {
          $cat_->items = [];
        }
      }

      $cat->cats = $cats;
    }

    return new GlobalResource([
      'module_info' => $request->module_info,
      'cats' => $main_cat,
      'items_free' => $all_items_free
    ]);
  }

  public function get_all_for_new(Request $request): GlobalResource
  {
    $apps = Model_sklad_items_module::get_apps();
    array_unshift($apps, array('id' => 0, 'name' => 'Не требуется'));

    $cats = Model_sklad_items_module::get_cats();
    $allergens = Model_sklad_items_module::get_allergens();
    $ed_izmer = Model_sklad_items_module::get_ed_izmer();
    $pf_list = Model_sklad_items_module::get_pf_list_is_show();
    $storages = Model_sklad_items_module::get_sklad_storage();

    $item = [
      'name' => '',
      'pf_id' => '',
      'vend_percent' => '0',
      'ed_izmer_id' => '0',
      'los_percent' => '0',
      'pq' => '',
      'percent' => '0',
      'art' => '',
      'is_show' => '1',
      'time_min' => '00:00',
      'time_dop_min' => '00:00',
      'time_min_other' => '00:00',
      'app_id' => '0',
      'name_for_vendor' => '',
      'w_pf' => '0',
      'w_trash' => '0',
      'w_item' => '0',
      'two_user' => '0',
      'max_count_in_m' => '0',
      'id' => '0',
      'cat_id' => '0',
      'min_count' => '0',
      'my_allergens' => [],
      'my_allergens_other' => []
    ];

    return new GlobalResource([
      'apps' => $apps,
      'item' => $item,
      'cats' => $cats,
      'allergens' => $allergens,
      'ed_izmer' => $ed_izmer,
      'pf_list' => $pf_list,
      'this_storages' => [],
      'storages' => $storages,
    ]);
  }

  public function get_search(Request $request): GlobalResource
  {

    $all_items = Model_sklad_items_module::get_all_items_search($request->data['item']);
    $all_items_free = Model_sklad_items_module::get_all_items_free_search($request->data['item']);
    $main_cat = Model_sklad_items_module::get_main_cats();

    foreach($main_cat as $key => $cat){

      $cats = Model_sklad_items_module::get_item_cats($cat->id);;

      $is_add = false;

      foreach($cats as $key_cat => $cat_){
        $cat_->items = [];

        foreach($all_items as $item ){
          if((int)$item->cat_id == (int)$cat_->id){
            $cat_->items[] = $item;
            $is_add = true;
          }
        }

        if(count($cat_->items) == 0){
          unset($cats[ $key_cat]);
        }

      }

      if(!$is_add){
        unset($main_cat[$key]);
      }

      $new_val = [];

      foreach($cats as $val){
        $new_val[] = $val;
      }

      if( count($new_val) > 0 ){
        $main_cat[$key]->cats = $new_val;
      }
    }

    return new GlobalResource([
      'cats' => $main_cat,
      'items_free' => $all_items_free
    ]);
  }

  public function get_one(Request $request): GlobalResource
  {
    $apps = Model_sklad_items_module::get_apps();
    array_unshift($apps, array('id' => 0, 'name' => 'Не требуется'));

    $ed_izmer = Model_sklad_items_module::get_item_ed_izmer($request->data['item_id']);
    $pf_list = Model_sklad_items_module::get_pf_list();
    $item = Model_sklad_items_module::get_one_item($request->data['item_id']);
    $this_storages = Model_sklad_items_module::get_item_storages($request->data['item_id']);
    $storages = Model_sklad_items_module::get_sklad_storage();
    $cats = Model_sklad_items_module::get_cats();
    $allergens = Model_sklad_items_module::get_allergens();
    $item->my_allergens = Model_sklad_items_module::get_item_allergens($request->data['item_id']);
    $item->my_allergens_other = Model_sklad_items_module::get_item_allergens_other($request->data['item_id']);

    return new GlobalResource([
      'apps' => $apps,
      'cats' => $cats,
      'ed_izmer' => $ed_izmer,
      'pf_list' => $pf_list,
      'item' => $item,
      'this_storages' => $this_storages,
      'storages' => $storages,
      'allergens' => $allergens
    ]);
  }

  public function save_check(Request $request): GlobalResource
  {
    $res = Model_sklad_items_module::update_item(
      $request->data['type'],
      $request->data['value'],
      $request->data['item_id']
    );

    if($res == 0 || $res > 0) {

      $storages = Model_sklad_items_module::get_item_storages($request->data['item_id']);
      $my_allergens = Model_sklad_items_module::get_item_allergens($request->data['item_id']);
      $my_allergens_other = Model_sklad_items_module::get_item_allergens_other($request->data['item_id']);

      $this->save_hist($request->login['login'], $request->data['item_id'], $storages, $my_allergens, $my_allergens_other, 'check');

      return new GlobalResource([
        'st' => true,
        'text' => 'Успешно сохранено'
      ]);

    } else {

      return new GlobalResource([
        'st' => false,
        'text' => 'Ошибка записи данных'
      ]);

    }
  }

  public function save_hist($login, $this_id, $storages, $my_allergens, $my_allergens_other, $type): void
  {
    $my = Helper::getInfoByMy($login);
    $hist_id = Model_sklad_items_module::insert_item_hist($my->id, date('Y-m-d'), $this_id);

    if($hist_id > 0){

      foreach($storages as $storage){
        Model_sklad_items_module::insert_sklad_storage_items_hist($hist_id, $type === 'check' ? $storage->id : $storage['id'], $this_id);
      }

      foreach($my_allergens as $allergen){
        Model_sklad_items_module::insert_items_allergens_hist($hist_id, $type === 'check' ? $allergen->id :$allergen['id'], $this_id);
      }

      foreach($my_allergens_other as $allergen){
        Model_sklad_items_module::insert_items_allergens_other_hist($hist_id, $type === 'check' ? $allergen->id :$allergen['id'], $this_id);
      }
    }
  }

  public function get_one_hist(Request $request): GlobalResource
  {
    $apps = Model_sklad_items_module::get_apps();
    array_unshift($apps, array('id' => 0, 'name' => 'Не требуется'));

    $ed_izmer = Model_sklad_items_module::get_item_ed_izmer($request->data['item_id']);
    $pf_list = Model_sklad_items_module::get_pf_list();
    $cats = Model_sklad_items_module::get_cats();
    $hist = Model_sklad_items_module::get_item_hist($request->data['item_id']);

    foreach($hist as $one_hist){
      $res_storage = Model_sklad_items_module::get_sklad_storage_items_hist($request->data['item_id'], $one_hist->id);
      $res_allergens = Model_sklad_items_module::get_items_allergens_hist($request->data['item_id'], $one_hist->id);
      $res_allergens_other = Model_sklad_items_module::get_items_allergens_other_hist($request->data['item_id'], $one_hist->id);
      $one_hist->user = Model_sklad_items_module::get_item_user_hist($one_hist->creator_id)->name;

      $one_hist->this_storages = implode(', ', array_column($res_storage, 'name'));
      $one_hist->my_allergens = implode(', ', array_column($res_allergens, 'name'));
      $one_hist->my_allergens_other = implode(', ', array_column($res_allergens_other, 'name'));

      $one_hist->apps = $apps;
      $one_hist->ed_izmer = $ed_izmer;
      $one_hist->pf_list = $pf_list;
      $one_hist->cats = $cats;
    };

    return new GlobalResource([
      'hist' => $hist
    ]);
  }

  public function check_art(Request $request): GlobalResource
  {
    $check_art = Model_sklad_items_module::get_one_art($request->data['id'], $request->data['art']);

    if($check_art){
      $arts = Model_sklad_items_module::get_arts($request->data['art']);

      return new GlobalResource([
        'st' => false,
        'arts' => $arts,
        'text' => 'Такой артикул уже задан у следующих позиций'
      ]);

    } else {

      return new GlobalResource([
        'st' => true
      ]);

    }
  }

  public function save_edit(Request $request): GlobalResource
  {
    $request->data['item']['time_min'] = str_replace(",", ":", $request->data['item']['time_min']);
    $request->data['item']['time_min'] = str_replace(" ", ":", $request->data['item']['time_min']);
    $request->data['item']['time_min'] = str_replace("-", ":", $request->data['item']['time_min']);

    $time = explode(':', $request->data['item']['time_min']);

    if(count($time) == 2){
      $time = (int)$time[0]*60 + (int)$time[1];
    } else {
      $time = (int)$time[0]*60;
    }

    $request->data['item']['time_dop_min'] = str_replace(",", ":", $request->data['item']['time_dop_min']);
    $request->data['item']['time_dop_min'] = str_replace(" ", ":", $request->data['item']['time_dop_min']);
    $request->data['item']['time_dop_min'] = str_replace("-", ":", $request->data['item']['time_dop_min']);

    $time_dop = explode(':', $request->data['item']['time_dop_min']);

    if(count($time_dop) == 2){
      $time_dop = (int)$time_dop[0]*60 + (int)$time_dop[1];
    } else {
      $time_dop = (int)$time_dop[0]*60;
    }

    $request->data['item']['time_min_other'] = str_replace(",", ":", $request->data['item']['time_min_other']);
    $request->data['item']['time_min_other'] = str_replace(" ", ":", $request->data['item']['time_min_other']);
    $request->data['item']['time_min_other'] = str_replace("-", ":", $request->data['item']['time_min_other']);

    if($request->data['item']['time_min_other'] == '0'){
      $time_other = 0;
    } else {
      $time_other = explode(':', $request->data['item']['time_min_other']);
      $time_other_mil = explode('.', $time_other[1]);

      if( count($time_other_mil) > 1 ){
        $time_other = (((int)$time_other[0]*60 + (int)$time_other[1]) * 1000) + (int)$time_other_mil[1];
      }else{
        $time_other = ((int)$time_other[0]*60 + (int)$time_other[1]) * 1000;
      }
    }

    $request->data['item']['name_for_vendor'] = str_replace('"', "'", $request->data['item']['name_for_vendor']);

    $request->data['item']['pq'] = str_replace(",", ".", $request->data['item']['pq']);

    Model_sklad_items_module::update_items_is_main_0($request->data['item']['art']);
    Model_sklad_items_module::update_items_is_main_1($request->data['main_item_id']);

    Model_sklad_items_module::delete_items_allergens($request->data['id']);
    Model_sklad_items_module::delete_items_allergens_other($request->data['id']);
    Model_sklad_items_module::delete_sklad_storage_items($request->data['id']);

    if(count($request->data['storages']) > 0) {
      foreach($request->data['storages'] as $storage){
        Model_sklad_items_module::insert_sklad_storage_items($storage['id'], $request->data['id']);
      }
    }

    if(count($request->data['my_allergens']) > 0) {
      foreach($request->data['my_allergens'] as $allergen){
        Model_sklad_items_module::insert_items_allergens($allergen['id'], $request->data['id']);
      }
    }

    if(count($request->data['my_allergens_other']) > 0) {
      foreach($request->data['my_allergens_other'] as $allergen){
        Model_sklad_items_module::insert_items_allergens_other($allergen['id'], $request->data['id']);
      }
    }

    $request->data['item']['name'] = addslashes($request->data['item']['name']);
    $request->data['item']['name_for_vendor'] = addslashes($request->data['item']['name_for_vendor']);
    $request->data['item']['app_id'] = empty($request->data['item']['app_id']) ? 0 : $request->data['item']['app_id'];

    $res = Model_sklad_items_module::update_one_item(
      $request->data['item']['name'],
      $request->data['pf_id'],
      $request->data['item']['vend_percent'],
      $request->data['item']['ed_izmer_id'],
      $request->data['item']['los_percent'],
      $request->data['item']['pq'],
      $request->data['item']['percent'],
      $request->data['item']['art'],
      $request->data['item']['is_show'],
      $request->data['cat_id'],
      $request->data['item']['time_min'],
      $time,
      $request->data['item']['time_dop_min'],
      $time_dop,
      $request->data['item']['time_min_other'],
      $time_other,
      $request->data['item']['app_id'],
      $request->data['item']['name_for_vendor'],
      $request->data['item']['w_pf'],
      $request->data['item']['w_trash'],
      $request->data['item']['w_item'],
      $request->data['item']['two_user'],
      $request->data['item']['min_count'],
      $request->data['item']['max_count_in_m'],
      date('Y-m-d'),
      $request->data['id']
    );

    if($res == 0 || $res > 0) {

      $this->save_hist($request->login['login'], $request->data['id'], $request->data['storages'], $request->data['my_allergens'], $request->data['my_allergens_other'], 'save');

      return new GlobalResource([
        'st' => true,
        'text' => 'Успешно сохранено'
      ]);

    } else {

      return new GlobalResource([
        'st' => false,
        'text' => 'Ошибка при сохранении'
      ]);

    }
  }

  public function save_new(Request $request): GlobalResource
  {
    $request->data['item']['time_min'] = str_replace(",", ":", $request->data['item']['time_min']);
    $request->data['item']['time_min'] = str_replace(" ", ":", $request->data['item']['time_min']);
    $request->data['item']['time_min'] = str_replace("-", ":", $request->data['item']['time_min']);

    $time = explode(':', $request->data['item']['time_min']);
    if(count($time) == 2){
      $time = (int)$time[0]*60 + (int)$time[1];
    }else{
      $time = (int)$time[0]*60;
    }

    $request->data['item']['time_dop_min'] = str_replace(",", ":", $request->data['item']['time_dop_min']);
    $request->data['item']['time_dop_min'] = str_replace(" ", ":", $request->data['item']['time_dop_min']);
    $request->data['item']['time_dop_min'] = str_replace("-", ":", $request->data['item']['time_dop_min']);

    $time_dop = explode(':', $request->data['item']['time_dop_min']);

    if(count($time_dop) == 2){
      $time_dop = (int)$time_dop[0]*60 + (int)$time_dop[1];
    } else {
      $time_dop = (int)$time_dop[0]*60;
    }

    $request->data['item']['time_min_other'] = str_replace(",", ":", $request->data['item']['time_min_other']);
    $request->data['item']['time_min_other'] = str_replace(" ", ":", $request->data['item']['time_min_other']);
    $request->data['item']['time_min_other'] = str_replace("-", ":", $request->data['item']['time_min_other']);

    $request->data['item']['name_for_vendor'] = str_replace('"', "'", $request->data['item']['name_for_vendor']);

    if($request->data['item']['time_min_other'] == 0 || $request->data['item']['time_min_other'] == '0'){
      $time_other = 0;
    } else {
      $time_other = explode(':', $request->data['item']['time_min_other']);
      $time_other_mil = explode('.', $time_other[1]);

      if(count($time_other_mil) > 1){
        $time_other = (((int)$time_other[0]*60 + (int)$time_other[1]) * 1000) + (int)$time_other_mil[1];
      } else {
        $time_other = ((int)$time_other[0]*60 + (int)$time_other[1]) * 1000;
      }
    }

    $request->data['item']['pq'] = str_replace(",", ".", $request->data['item']['pq']);

    $items_list = Model_sklad_items_module::get_items_list();

    $request->data['item']['name'] = addslashes($request->data['item']['name']);

    foreach($items_list as $pf_item){
      if(mb_strtolower($pf_item->name) === mb_strtolower($request->data['item']['name'])){
        return new GlobalResource([
          'st' => false,
          'text' => 'Товар с таким названием уже существует'
        ]);
      }
    }

    $request->data['item']['name'] = addslashes($request->data['item']['name']);
    $request->data['item']['name_for_vendor'] = addslashes($request->data['item']['name_for_vendor']);
    $request->data['item']['app_id'] = empty($request->data['item']['app_id']) ? 0 : $request->data['item']['app_id'];

    $this_id = Model_sklad_items_module::insert_new_item(
      $request->data['item']['name'],
      $request->data['item']['name_for_vendor'],
      $request->data['cat_id'],
      $request->data['pf_id'],
      $request->data['item']['art'],
      $request->data['item']['ed_izmer_id'],
      $request->data['item']['los_percent'],
      $request->data['item']['pq'],
      $request->data['item']['percent'],
      $request->data['item']['vend_percent'],
      $request->data['item']['time_min'],
      $time,
      $request->data['item']['time_dop_min'],
      $time_dop,
      $request->data['item']['time_min_other'],
      $time_other,
      $request->data['item']['app_id'],
      $request->data['item']['w_pf'],
      $request->data['item']['w_trash'],
      $request->data['item']['w_item'],
      $request->data['item']['two_user'],
      $request->data['item']['max_count_in_m'],
      date('Y-m-d')
    );

    if($this_id > 0){

      if(count($request->data['storages']) > 0) {
        foreach($request->data['storages'] as $storage){
          Model_sklad_items_module::insert_sklad_storage_items($storage['id'], $this_id);
        }
      }

      if(count($request->data['my_allergens']) > 0) {
        foreach($request->data['my_allergens'] as $allergen){
          Model_sklad_items_module::insert_items_allergens($allergen['id'], $this_id);
        }
      }

      if(count($request->data['my_allergens_other']) > 0) {
        foreach($request->data['my_allergens_other'] as $allergen){
          Model_sklad_items_module::insert_items_allergens_other($allergen['id'], $this_id);
        }
      }

      Model_sklad_items_module::update_items_is_main_0($request->data['item']['art']);

      if((int)$request->data['main_item_id'] == -1 ){
        $request->data['main_item_id'] = $this_id;
      }

      Model_sklad_items_module::update_items_is_main_1($request->data['main_item_id']);

      $this->save_hist($request->login['login'], $this_id, $request->data['storages'], $request->data['my_allergens'], $request->data['my_allergens_other'], 'save');

      return new GlobalResource([
        'st' => true,
        'text' => 'Успешно сохранено'
      ]);

    } else {

      return new GlobalResource([
        'st' => false,
        'text' => 'Ошибка записи данных, попробуй еще раз'
      ]);

    }
  }


}
