<?php

namespace App\Http\Controllers;

use App\Http\Resources\GlobalResource;
use App\Models\Model_site_price_level;
use Illuminate\Http\Request;

use App\Http\Controllers\Api\PriceLevelExport;
use App\Http\Controllers\Api\PriceLevelImport;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class Controller_site_price_level extends Controller
{
    public function get_all(Request $request): GlobalResource
    {
      $cities = Model_site_price_level::get_cities();
      $levels =  Model_site_price_level::get_all_price_level();

      array_unshift($cities, array('id' => -1, 'name' => 'Все города'));

      return new GlobalResource([
        'module_info' => $request->module_info,
        'cities' => $cities,
        'levels' => $levels
      ]);
    }

    public function get_all_for_new(Request $request): GlobalResource
    {
      $cities = Model_site_price_level::get_cities();

      return new GlobalResource([
        'cities' => $cities
      ]);
    }

    public function save_new(Request $request): GlobalResource
    {
      $request->data['name'] = addslashes($request->data['name']);

      $level_id = Model_site_price_level::insert_new_level(
        $request->data['name'],
        $request->data['city_id'],
        $request->data['date_start'],
        date('Y_m_d_H_i_s')
      );

      if ($level_id > 0) {

        $level_items = Model_site_price_level::insert_new_level_items($level_id);

        if ($level_items > 0) {

          return new GlobalResource([
            'st' => true,
            'level_id' => $level_id
          ]);

        } else {

          return new GlobalResource([
            'st' => false,
            'text' => 'Ошибка записи'
          ]);

        }

      } else {

        return new GlobalResource([
          'st' => false,
          'text' => 'Ошибка записи'
        ]);

      }

    }

    public function get_one(Request $request): GlobalResource
    {

      $cities = Model_site_price_level::get_cities();
      $cats = Model_site_price_level::get_all_cats();
      $items = Model_site_price_level::get_all_level_items($request->data['level_id']);
      $level = Model_site_price_level::get_one_level($request->data['level_id']);

      foreach($cats as $cat){
        $cat->items = Model_site_price_level::get_all_items_in_cats($cat->id);
      }

      foreach($cats as $cat){
        foreach($cat->items as $item){
          foreach($items as $pli){
            if((int)$pli->item_id == (int)$item->id){
              $item->price = $pli->price;
            }
          }
        }
      }

      return new GlobalResource([
        'module_info' => $request->module_info,
        'cats' => $cats,
        'cities' => $cities,
        'level' => $level
      ]);
    }

    public function save_one_price(Request $request): GlobalResource
    {

      if(empty($request->data['value'])){
        $request->data['value'] = 0;
      }

      $res = Model_site_price_level::update_one_price($request->data['level_id'], $request->data['item_id'], $request->data['value']);

      return new GlobalResource([
        'st' => $res
      ]);
    }

    public function save_edit(Request $request): GlobalResource
    {
      Model_site_price_level::delete_all_items_level($request->data['level_id']);

      foreach($request->data['items'] as $item){

        if(empty($item['price'])){
          $item['price'] = 0;
        }

        Model_site_price_level::insert_all_level_items($item['id'], $request->data['level_id'], $item['price']);

      }

      $request->data['name'] = addslashes($request->data['name']);

      $res = Model_site_price_level::update_level(
        $request->data['level_id'],
        $request->data['name'],
        $request->data['city_id'],
        $request->data['date_start'],
        date('Y_m_d_H_i_s')
      );

      if($res == 0 || $res > 0) {

        return new GlobalResource([
          'st' => true,
          'text' => 'Успешно сохранено',
          'level_id' => $request->data['level_id']
        ]);

      } else {

        return new GlobalResource([
          'st' => false,
          'text' => 'Ошибка записи данных'
        ]);

      }

    }

    public function export_file_xls(): BinaryFileResponse
    {
      return Excel::download(new PriceLevelExport, 'form_price_level.xlsx');
    }

    public function import_file_xls(Request $request): GlobalResource
    {
      $import = new PriceLevelImport;
      Excel::import($import, $request->file);
      return $import->get_result_save_import_level();
    }

}
