<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Model_site_price_level extends Model
{
    use HasFactory;

    static function get_cities(): array
    {
      return DB::select(/** @lang text */ '
        SELECT
          `id`,
          `name`
        FROM
          jaco_main_rolls.`cities`
        WHERE
          `is_show`=1
      ') ?? [];
    }

    static function get_all_price_level(): array
    {
      return DB::select(/** @lang text */ '
        SELECT
          pln.*,
          c.`name` as city_name
        FROM
          jaco_site_rolls.`price_level_new` pln
           LEFT JOIN jaco_main_rolls.`cities` c
            ON
          c.`id`=pln.`city_id`
      ') ?? [];
    }

    static function get_all_cats(): array
    {
      return DB::select(/** @lang text */ '
        SELECT
          `id`,
          `name`
				FROM
					jaco_site_rolls.`category`
				ORDER BY
					`sort` ASC
      ') ?? [];
    }

    static function insert_new_level(string $name, int $city_id, string $date_start, string $date_update): int
    {
      DB::insert(/** @lang text */ '
          INSERT INTO jaco_site_rolls.`price_level_new` (name, city_id, date_start, date_time_update)
          VALUES(
            "'.$name.'",
            "'.$city_id.'",
            "'.$date_start.'",
            "'.$date_update.'"
          )
        ');

      return DB::getPdo()->lastInsertId();
    }

    static function insert_new_level_items(int $level_id): int
    {
      DB::insert(/** @lang text */ '
        INSERT INTO jaco_site_rolls.`price_level_items_new` (item_id, level_id, price)
          SELECT
            `id`,
            "'.$level_id.'" as level_id,
            "0" as price
          FROM
            jaco_site_rolls.`items`
          WHERE
            `is_show`=1
      ');

      return DB::getPdo()->lastInsertId();
    }

    static function get_all_level_items(int $level_id): array
    {
      return DB::select(/** @lang text */ '
				SELECT
					pli.*,
					i.`name`
				FROM
					jaco_site_rolls.`price_level_items_new` pli
					LEFT JOIN jaco_site_rolls.`items` i
						ON
            i.`id`=pli.`item_id`
				WHERE
					pli.`level_id`="'.$level_id.'"
      ') ?? [];
    }

    static function get_all_items_in_cats(int $cat_id): array
    {
      return DB::select(/** @lang text */ '
        SELECT
          `id`,
          `name`
        FROM
          jaco_site_rolls.`items`
        WHERE
          `category_id`="'.$cat_id.'"
            AND
          `is_show`=1
        ORDER BY
          `name` ASC
      ') ?? [];
    }

    static function get_one_level(int $level_id): object
    {
      return DB::selectOne(/** @lang text */ '
        SELECT
          *
        FROM
          jaco_site_rolls.`price_level_new`
        WHERE
          `id`="'.$level_id.'"
      ');
    }

    static function update_one_price(int $level_id, int $item_id, string $value): bool
    {
      return DB::update(/** @lang text */'
        UPDATE
          jaco_site_rolls.`price_level_items_new`
        SET
          `price`='.$value.'
        WHERE
          `level_id`='.$level_id.'
            AND
          `item_id`='.$item_id.'
      ');
    }

    static function delete_all_items_level(int $level_id): void
    {
      DB::select(/** @lang text */ '
        DELETE FROM
          jaco_site_rolls.`price_level_items_new`
        WHERE
          `level_id`=:level_id
      ', ['level_id' => $level_id]);
    }

    static function insert_all_level_items(int $item_id, int $level_id, int $price): void
    {
      DB::insert(/** @lang text */ '
        INSERT INTO jaco_site_rolls.`price_level_items_new` (item_id, level_id, price)
        VALUES(
          :item_id,
          :level_id,
          :price
        )', ['item_id' => $item_id, 'level_id' => $level_id, 'price' => $price]
      );
    }

    static function update_level(int $level_id, string $name, int $city_id, string $date_start, string $date_update): bool
    {
      return DB::update(/** @lang text */'
        UPDATE
          jaco_site_rolls.`price_level_new`
        SET
          `name`="'.$name.'",
          `city_id`="'.$city_id.'",
          `date_start`="'.$date_start.'",
          `date_time_update`="'.$date_update.'"
        WHERE
          `id`='.$level_id.'
      ');
    }

    static function get_all_items(): array
    {
      return DB::select(/** @lang text */ '
        SELECT
          i.`id`,
          i.`name`
        FROM
          jaco_site_rolls.`items` i
          LEFT JOIN jaco_site_rolls.`category_new` c2
            ON
              i.`category_id2`=c2.`id`
        WHERE
          i.`is_show`=1
        ORDER BY
          c2.`sort_app` ASC,
          i.`sort` ASC
      ') ?? [];
    }

    static function get_city_by_name(string $name): object
    {
      return DB::selectOne(/** @lang text */ '
        SELECT
          `id`
        FROM
          jaco_main_rolls.`cities`
        WHERE
          `is_show`=1
            AND
          `name`=:name
        ', ['name' => $name]);
    }

}
