<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Model_site_page_text extends Model
{
    use HasFactory;

    static function get_cities(): array
    {
      $cities = DB::select(/** @lang text */ '
        SELECT
          `id`,
          `name`
        FROM
          jaco_main_rolls.`cities`
        WHERE
          `is_show`=1
      ') ?? [];

      array_unshift($cities, array('id' => -1, 'name' => 'Все города'));

      return $cities;
    }

    static function get_all_pages(): array
    {
      return DB::select(/** @lang text */ '
        SELECT
          pt.*,
          if( pt.`city_id` = -1, "Все города", c.`name` ) as city_name,
          p.`name` page_name
        FROM
          jaco_site_rolls.`page_text` pt
          LEFT JOIN jaco_main_rolls.`cities` c
              ON
            c.`id`=pt.`city_id`
          LEFT JOIN jaco_site_rolls.`pages` p
              ON
            pt.`page_id`=p.`id`
        WHERE
          p.`for_del`=0
      ') ?? [];
    }

    static function get_page(int $page_id): object
    {
      return DB::selectOne(/** @lang text */ '
        SELECT
          pt.*,
          c.`name` city_name,
          p.`name` page_name
        FROM
          jaco_site_rolls.`page_text` pt
          LEFT JOIN jaco_main_rolls.`cities` c
              ON
            c.`id`=pt.`city_id`
          LEFT JOIN jaco_site_rolls.`pages` p
              ON
            pt.`page_id`=p.`id`
        WHERE
          pt.`id`=:page_id
      ', ['page_id' => $page_id]);
    }

    static function get_data_page(int $id): object
    {
      return DB::selectOne(/** @lang text */ '
        SELECT
          `id`,
          `name`,
          `link`
        FROM
          jaco_site_rolls.`pages`
        WHERE
          `id`=:id
      ', ['id' => $id]);
    }

    static function get_name_page(string $name): object|null
    {
      return DB::selectOne(/** @lang text */ '
        SELECT
          `id`
        FROM
          jaco_site_rolls.`pages`
        WHERE
          `name`=:name
      ', ['name' => $name]);
    }

    static function get_all_pages_from_page_text(): array
    {
      return DB::select(/** @lang text */ '
        SELECT
          pt.`page_id`,
          pt.`city_id`
        FROM
          jaco_site_rolls.`page_text` pt
            LEFT JOIN jaco_site_rolls.`pages` p
              ON
            pt.`page_id`=p.`id`
        WHERE
          p.`for_del`=0
      ') ?? [];
    }

    static function insert_new_page(string $name, string $link): int
    {
      DB::insert(/** @lang text */ '
        INSERT INTO jaco_site_rolls.`pages` (name, link)
        VALUES(
          "'.$name.'",
          "'.$link.'"
        )
      ');

      return DB::getPdo()->lastInsertId();
    }

    static function insert_new_page_text(int $page_id, int $city_id, string $page_h, string $page_title, string $page_description, string $page_text, string $date_update): int
    {
      DB::insert(/** @lang text */ '
        INSERT INTO jaco_site_rolls.`page_text` (page_id, city_id, page_h, title, description, content, date_time_update)
        VALUES(
          "'.$page_id.'",
          "'.$city_id.'",
          "'.$page_h.'",
          "'.$page_title.'",
          "'.$page_description.'",
          "'.$page_text.'",
          "'.$date_update.'"
        )
      ');

      return DB::getPdo()->lastInsertId();
    }

    static function update_page_text(int $page_id, int $city_id, string $page_h, string $page_title, string $page_description,string $date_update, int $id): int
    {
      return DB::update(/** @lang text */'
        UPDATE
          jaco_site_rolls.`page_text`
        SET
          `page_id`="'.$page_id.'",
          `city_id`="'.$city_id.'",
          `page_h`="'.$page_h.'",
          `title`="'.$page_title.'",
          `description`="'.$page_description.'",
          `title`="'.$page_title.'",
          `date_time_update`="'.$date_update.'"
        WHERE
          `id`="'.$id.'"
      ');
    }

    static function update_page(string $name, string $link, int $id): int
    {
      return DB::update(/** @lang text */'
        UPDATE
          jaco_site_rolls.`pages`
        SET
          `name`="'.$name.'",
          `link`="'.$link.'"
        WHERE
          `id`="'.$id.'"
      ');
    }

}
