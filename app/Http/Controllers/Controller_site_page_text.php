<?php

namespace App\Http\Controllers;

use App\Http\Resources\GlobalResource;
use App\Models\Model_site_page_text;
use Illuminate\Http\Request;

class Controller_site_page_text extends Controller
{
  public function get_all(Request $request): GlobalResource
  {
    $cities = Model_site_page_text::get_cities();
    $pages = Model_site_page_text::get_all_pages();

    return new GlobalResource([
      'module_info' => $request->module_info,
      'cities' => $cities,
      'pages' => $pages
    ]);
  }

  public function get_all_for_new(Request $request): GlobalResource
  {
    $cities = Model_site_page_text::get_cities();

    return new GlobalResource([
      'cities' => $cities
    ]);
  }

  public function get_one(Request $request): GlobalResource
  {
    $cities = Model_site_page_text::get_cities();
    $page = Model_site_page_text::get_page($request->data['id']);

    if ($page) {
      $page_data = Model_site_page_text::get_data_page($page->page_id);
      $page->link = $page_data->link;
    }

    return new GlobalResource([
      'page' => $page,
      'cities' => $cities
    ]);
  }

  public function save_new(Request $request): GlobalResource
  {
    $request->data['page_name'] = addslashes($request->data['page_name']);
    $page_name = Model_site_page_text::get_name_page($request->data['page_name']);

    if ($page_name) {
      $all_pages_text = Model_site_page_text::get_all_pages_from_page_text();

      foreach ($all_pages_text as $item) {
        if ((int)$item->page_id == (int)$page_name->id && (int)$item->city_id == (int)$request->data['city_id']) {
          return new GlobalResource([
            'st' => false,
            'text' => 'Такая страница уже существует'
          ]);
        }
      }

    }

    $page_id = Model_site_page_text::insert_new_page(
      $request->data['page_name'],
      $request->data['link']
    );

    if ($page_id > 0) {

      $request->data['page_h'] = addslashes($request->data['page_h']);
      $request->data['page_title'] = addslashes($request->data['page_title']);
      $request->data['page_description'] = addslashes($request->data['page_description']);

      $id = Model_site_page_text::insert_new_page_text(
        $page_id,
        $request->data['city_id'],
        $request->data['page_h'],
        $request->data['page_title'],
        $request->data['page_description'],
        $request->data['page_text'],
        date('Y-m-d')
      );

      if ($id > 0) {

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

    } else {

      return new GlobalResource([
        'st' => false,
        'text' => 'Ошибка записи данных'
      ]);

    }
  }

  public function save_edit(Request $request): GlobalResource
  {

    $request->data['page_h'] = addslashes($request->data['page_h']);
    $request->data['page_title'] = addslashes($request->data['page_title']);
    $request->data['page_description'] = addslashes($request->data['page_description']);
    $request->data['page_name'] = addslashes($request->data['page_name']);

    $res_page = Model_site_page_text::update_page(
      $request->data['page_name'],
      $request->data['link'],
      $request->data['page_id']
    );

    $res_page_text = Model_site_page_text::update_page_text(
      $request->data['page_id'],
      $request->data['city_id'],
      $request->data['page_h'],
      $request->data['page_title'],
      $request->data['page_description'],
      date('Y-m-d'),
      $request->data['id']
    );

    if(($res_page == 0 || $res_page > 0) && ($res_page_text == 0 || $res_page_text > 0)) {

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
}
