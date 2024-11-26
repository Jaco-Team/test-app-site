<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Model_site_push extends Model
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

    static function get_push_active(): array
    {
      return DB::select(/** @lang text */ '
        SELECT
          *
        FROM
          jaco_site_rolls.`push`
        WHERE
          `is_active`=1
      ') ?? [];
    }

    static function get_push_none_active(): array
    {
      return DB::select(/** @lang text */ '
        SELECT
          *
        FROM
          jaco_site_rolls.`push`
        WHERE
          `is_active`=0
      ') ?? [];
    }

    static function get_city_name_push(array $active): array
    {
      foreach($active as $push){

        $res_city = Model_site_push::get_city($push->id);

        $res_all_city = Model_site_push::get_all_city($push->id);

        $res = count($res_all_city) > 0 ? $res_all_city : $res_city;

        if(count($res) === 1) {
          $push->city_name = head($res)->name;
          $push->city_id = [head($res)->id];
        }

        if(count($res) > 1) {
          $push->city_id = [];
          $push->city_name = '';

          foreach($res as $city){
            $push->city_name .= $city->name.', ';
            $push->city_id[] = $city->id;
          }

          $push->city_name = rtrim($push->city_name, ", ");
        }

      }

      return $active;
    }

    static function get_city(int $push_id): array
    {
      return DB::select(/** @lang text */ '
        SELECT
          c.`id` as id,
          c.`name` as name
        FROM
          jaco_site_rolls.`push_cities` pc
          LEFT JOIN jaco_main_rolls.`cities` c
            ON
          c.`id`=pc.`city_id`
        WHERE
          pc.`push_id`=:push_id
      ', ['push_id' => $push_id]) ?? [];
    }

    static function get_all_city(int $push_id): array
    {
      return DB::select(/** @lang text */ '
        SELECT
          "-1" id,
          "Все города" name
        FROM
          jaco_site_rolls.`push_cities` pc
        WHERE
          pc.`push_id`=:push_id
        AND
          pc.`city_id`=-1
      ', ['push_id' => $push_id]) ?? [];
    }

    static function get_all_items(): array
    {
      return DB::select(/** @lang text */ '
        SELECT
					`id`,
					`name`,
          "item" type
				FROM
					jaco_site_rolls.`items`
				WHERE
					`is_show`=1
        ') ?? [];
    }

    static function get_banners(): array
    {
      return DB::select(/** @lang text */ '
        SELECT
					`id`,
					`name`,
          `city_id`,
          "banner" type
				FROM
					jaco_site_rolls.`banners_new`
				WHERE
					`date_end`>="'.date('Y-m-d').'"
						AND
					`is_active`=1
        ORDER BY
					`city_id`
      ') ?? [];
    }

    static function get_this_push(int $push_id): object
    {
      return DB::selectOne(/** @lang text */ '
        SELECT
					*
				FROM
					jaco_site_rolls.`push`
				WHERE
					`id`=:push_id
      ', ['push_id' => $push_id]);
    }

    static function save_check_push(int $push_id, string $value): bool
    {
      return DB::update(/** @lang text */'
          UPDATE
            jaco_site_rolls.`push`
          SET
            `is_active`='.$value.'
          WHERE
            `id`='.$push_id.'
        ');
    }

    static function insert_new_push(string $name, string $date_start, string $time_start, int $is_send, int $is_auth, string $title, string $text, int $type, int $is_active, int $item_id, int $ban_id, string $date_update): int
    {
      DB::insert(/** @lang text */ '
          INSERT INTO jaco_site_rolls.`push` (name, date_start, time_start, is_send, is_auth, title, text, type, is_active, item_id, ban_id, date_update)
          VALUES(
            "'.$name.'",
            "'.$date_start.'",
            "'.$time_start.'",
            "'.$is_send.'",
            "'.$is_auth.'",
            "'.$title.'",
            "'.$text.'",
            "'.$type.'",
            "'.$is_active.'",
            "'.$item_id.'",
            "'.$ban_id.'",
            "'.$date_update.'"
          )
        ');

      return DB::getPdo()->lastInsertId();
    }

    static function insert_push_cities(int $id, int $city_id)
    {
      DB::insert(/** @lang text */ '
        INSERT INTO jaco_site_rolls.`push_cities` (push_id, city_id)
        VALUES(
          :id,
          :city_id
        )', ['id' => $id, 'city_id' => $city_id]
      );
    }

    static function update_edit_push(string $name, string $date_start, string $time_start, int $is_send, int $is_auth, string $title, string $text, int $type, int $is_active, int $item_id, int $ban_id, string $date_update, int $push_id)
    {
      return DB::update(/** @lang text */'
        UPDATE
          jaco_site_rolls.`push`
        SET
          `name`="'.$name.'",
          `date_start`="'.$date_start.'",
          `time_start`="'.$time_start.'",
          `is_send`="'.$is_send.'",
          `is_auth`="'.$is_auth.'",
          `title`="'.$title.'",
          `text`="'.$text.'",
          `type`="'.$type.'",
          `is_active`="'.$is_active.'",
          `item_id`="'.$item_id.'",
          `ban_id`="'.$ban_id.'",
          `date_update`="'.$date_update.'"
        WHERE
          `id`="'.$push_id.'"
      ');
    }

    static function delete_push_cities(int $push_id)
    {
      DB::delete(/** @lang text */ '
        DELETE FROM
          jaco_site_rolls.`push_cities`
        WHERE
          `push_id`=:push_id
      ', ['push_id' => $push_id]);
    }

}
