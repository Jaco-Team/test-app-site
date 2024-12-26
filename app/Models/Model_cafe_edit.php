<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Model_cafe_edit extends Model
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

    static function get_one_point(int $point_id): object
    {
      return DB::selectOne(/** @lang text */ '
        SELECT
          `id`,
          `name`,
          `addr`,
          `raion`,
          `city_id`,
          `organization`,
          `inn`,
          `ogrn`,
          `kpp`,
          `full_addr`,
          `is_active`,
          `sort`,
          `phone_upr`,
          `phone_man`,
          `k_pizza`,
          `k_pizza_kux`,
          `k_rolls_kux`,
          `driver_price`,
          `dir_price`,
          `price_per_lv`,
          `priority_pizza`,
          `priority_order`,
          `rolls_pizza_dif`,
          `cook_common_stol`,
          `summ_driver`,
          `summ_driver_min`,
          `cafe_handle_close`
        FROM
          jaco_main_rolls.`points`
        WHERE
          `id`=:point_id
      ', ['point_id' => $point_id]);
    }

    static function get_actual_time_list(int $point_id, string $date): array
    {
      return DB::select(/** @lang text */ '
        SELECT
           td.*,
				   CONCAT(pz.name, " ( зона ", pz.id, " ) ") as name
         FROM
           jaco_site_rolls.`time_dev` td
				LEFT JOIN
					jaco_main_rolls.`points_zone` pz
					ON td.zone_id = pz.id
        WHERE
          pz.`point_id` = :point_id
            AND
          dow = :date
      ', ['point_id' => $point_id, 'date' => $date]) ?? [];
    }

    static function get_dop_time_list(int $point_id, string $date): array
    {
      return DB::select(/** @lang text */ '
        SELECT
          td.*,
          CONCAT(pz.name, " ( зона ", pz.id, " ) ") as name
        FROM
          jaco_site_rolls.`time_dev_other` td
        LEFT JOIN
          jaco_main_rolls.`points_zone` pz
            ON
          td.zone_id = pz.id
            WHERE
          pz.`point_id` = :point_id
            AND
          date = :date
      ', ['point_id' => $point_id, 'date' => $date]) ?? [];
    }

    static function get_one_zone(int $point_id): array
    {
      return DB::select(/** @lang text */ '
        SELECT
					pz.`id`,
					pz.`name` as zone_name,
					pz.`point_id`,
					pz.`sum_div`,
					pz.`sum_div_driver`,
					pz.`free_drive`,
					p.`addr` as point_name,
					c.`name` as city_name,
					pz.`zone`,
					p.`xy_point`,
					pz.`is_active`
				FROM
					jaco_main_rolls.`points_zone` pz
					LEFT JOIN jaco_main_rolls.`points` p
						ON
							p.`id`=pz.`point_id`
					LEFT JOIN jaco_main_rolls.`cities` c
						ON
							c.`id`=p.`city_id`
				WHERE
					pz.`point_id`=:point_id
				ORDER BY
					c.`id`,
					p.`sort`
      ', ['point_id' => $point_id]) ?? [];
    }

    static function get_other_zones(int $city_id, int $point_id): array
    {
      return DB::select(/** @lang text */ '
        SELECT
					pz.`zone`,
					pz.`name`,
					pz.`id`
				FROM
					jaco_main_rolls.`points_zone` pz
					LEFT JOIN jaco_main_rolls.`points` p
						ON
          pz.`point_id`=p.`id`
				WHERE
          p.`city_id`=:city_id
            AND
					p.`is_active`=1
						AND
					pz.`point_id`!=:point_id
      ', ['city_id' => $city_id, 'point_id' => $point_id]) ?? [];
    }

    static function update_point_info(int $city_id, string $addr, string $raion, int $sort, string $organization, int $inn, int $ogrn, int $kpp, string $full_addr, int $is_active, string $phone_upr, string $phone_man, int $point_id): int
    {
      return DB::update(/** @lang text */'
          UPDATE
            jaco_main_rolls.`points`
          SET
            `city_id`="'.$city_id.'",
            `addr`="'.$addr.'",
            `raion`="'.$raion.'",
            `sort`="'.$sort.'",
            `organization`="'.$organization.'",
            `inn`="'.$inn.'",
            `ogrn`="'.$ogrn.'",
            `kpp`="'.$kpp.'",
            `full_addr`="'.$full_addr.'",
            `is_active`="'.$is_active.'",
            `phone_upr`="'.$phone_upr.'",
            `phone_man`="'.$phone_man.'"
          WHERE
            `id`="'.$point_id.'"
        ');
    }

    static function update_point_sett(int $active_cafe, int $cook_common_stol, int $summ_driver, int $summ_driver_min, int $priority_order, int $priority_pizza, int $rolls_pizza_dif, int $point_id): int
    {
      return DB::update(/** @lang text */'
        UPDATE
          jaco_main_rolls.`points`
        SET
          `cafe_handle_close`="'.$active_cafe.'",
          `cook_common_stol`="'.$cook_common_stol.'",
          `summ_driver`="'.$summ_driver.'",
					`summ_driver_min`="'.$summ_driver_min.'",
					`priority_order`="'.$priority_order.'",
					`priority_pizza`="'.$priority_pizza.'",
					`rolls_pizza_dif`="'.$rolls_pizza_dif.'"
        WHERE
          `id`="'.$point_id.'"
      ');
    }

    static function get_point_info(int $point_id): object
    {
      return DB::selectOne(/** @lang text */ '
          SELECT
            `cafe_handle_close`
          FROM
            jaco_main_rolls.`points`
          WHERE
            `id`=:point_id
        ', ['point_id' => $point_id]);
    }

    static function update_cafe_close_history(string $this_date, int $point_id): void
    {
      DB::update(/** @lang text */'
        UPDATE
          jaco_site_rolls.`cafe_close_history`
        SET
          `date_time_open`="'.$this_date.'"
        WHERE
          `point_id`="'.$point_id.'"
            AND
          `date_time_open` IS NULL
      ');
    }

    static function insert_event_new_hist(int $user_id, int $point_id, string $this_date, string $comment): void
    {
      DB::insert(/** @lang text */ '
        INSERT INTO
          jaco_site_rolls.`event_new_hist` (user_id, type_active, type, point_id, date, every, comment)
        VALUES (
          "'.$user_id.'",
          "2",
          "4",
          "'.$point_id.'",
          "'.$this_date.'",
          "0",
          "'.$comment.'"
        )
      ');
    }

    static function delete_point_rate(int $point_id): void
    {
      DB::delete(/** @lang text */ '
        DELETE FROM
          jaco_main_rolls.`points_rate`
        WHERE
          `point_id`=:point_id
      ', ['point_id' => $point_id]);
    }

    static function insert_point_rate(int $point_id, string $date_start, string $k_pizza, string $k_pizza_kux, string $k_rolls_kux): string|false
    {
      DB::insert(/** @lang text */ '
          INSERT INTO
            jaco_main_rolls.`points_rate` (point_id, date_start, k_pizza, k_pizza_kux, k_rolls_kux)
          VALUES (
            "'.$point_id.'",
            "'.$date_start.'",
            "'.$k_pizza.'",
            "'.$k_pizza_kux.'",
            "'.$k_rolls_kux.'"
          )
        ');

      return DB::getPdo()->lastInsertId();
    }

    static function insert_point_rate_hist(int $point_id, int $creator_id, string $date_start, string $k_pizza, string $k_pizza_kux, string $k_rolls_kux, string $date_time_update): void
    {
      DB::insert(/** @lang text */ '
        INSERT INTO
          jaco_main_rolls.`points_rate_hist` (point_id, creator_id, date_start, k_pizza, k_pizza_kux, k_rolls_kux, date_time_update)
        VALUES (
          "'.$point_id.'",
          "'.$creator_id.'",
          "'.$date_start.'",
          "'.$k_pizza.'",
          "'.$k_pizza_kux.'",
          "'.$k_rolls_kux.'",
          "'.$date_time_update.'"
        )
      ');
    }

    static function update_dir_price(int $dir_price, int $price_per_lv, string $this_date, int $point_id): void
    {
      DB::update(/** @lang text */'
        UPDATE
          jaco_main_rolls.`cafe_smena_info` csi,
          jaco_main_rolls.`cafe_smena` cs,
          jaco_main_rolls.`appointment` app
        SET
          csi.`dir_price`="'.$dir_price.'",
          csi.`price_per_lv`="'.$price_per_lv.'"
        WHERE
          cs.`id`=csi.`smena_id`
              AND
          app.`id`=csi.`app_id`
              AND
          app.`type`="dir"
              AND
          csi.`date`>="'.$this_date.'"
              AND
          cs.`point_id`="'.$point_id.'"
      ');
    }

    static function update_driver_price(int $driver_price, string $this_date, int $point_id): void
    {
      DB::update(/** @lang text */'
        UPDATE
          jaco_main_rolls.`cafe_smena_info` csi,
          jaco_main_rolls.`cafe_smena` cs,
          jaco_main_rolls.`appointment` app
        SET
          csi.`driver_price`="'.$driver_price.'"
        WHERE
          cs.`id`=csi.`smena_id`
              AND
          app.`id`=csi.`app_id`
              AND
          app.`type`="driver"
              AND
          csi.`date`>"'.$this_date.'"
              AND
          cs.`point_id`="'.$point_id.'"
      ');
    }

    static function delete_point_pay(int $point_id): void
    {
      DB::delete(/** @lang text */ '
        DELETE FROM
          jaco_main_rolls.`points_pay`
        WHERE
          `point_id`=:point_id
      ', ['point_id' => $point_id]);
    }

    static function insert_point_pay(int $point_id, string $date_start, string $dir_price, string $price_per_lv, string $driver_price): string|false
    {
      DB::insert(/** @lang text */ '
        INSERT INTO
          jaco_main_rolls.`points_pay` (point_id, date_start, dir_price, price_per_lv, driver_price)
        VALUES (
          "'.$point_id.'",
          "'.$date_start.'",
          "'.$dir_price.'",
          "'.$price_per_lv.'",
          "'.$driver_price.'"
        )
      ');

      return DB::getPdo()->lastInsertId();
    }

    static function insert_point_pay_hist(int $point_id, int $creator_id, string $date_start, string $dir_price, string $price_per_lv, string $driver_price, string $date_time_update): void
    {
      DB::insert(/** @lang text */ '
        INSERT INTO
          jaco_main_rolls.`points_pay_hist` (point_id, creator_id, date_start, dir_price, price_per_lv, driver_price, date_time_update)
        VALUES (
          "'.$point_id.'",
          "'.$creator_id.'",
          "'.$date_start.'",
          "'.$dir_price.'",
          "'.$price_per_lv.'",
          "'.$driver_price.'",
          "'.$date_time_update.'"
        )
      ');
    }

    static function insert_point_info_hist(int $point_id, int $creator_id, int $city_id, string $addr, string $raion, int $sort, string $organization, string $inn, string $ogrn, string $kpp, string $full_addr, int $is_active, string $phone_upr, string $phone_man, string $date_time_update): void
    {
      DB::insert(/** @lang text */ '
        INSERT INTO
          jaco_main_rolls.`points_info_hist` (point_id, creator_id, city_id, addr, raion, sort, organization, inn, ogrn, kpp, full_addr, is_active, phone_upr, phone_man, date_time_update)
        VALUES (
          "'.$point_id.'",
          "'.$creator_id.'",
          "'.$city_id.'",
          "'.$addr.'",
          "'.$raion.'",
          "'.$sort.'",
          "'.$organization.'",
          "'.$inn.'",
          "'.$ogrn.'",
          "'.$kpp.'",
          "'.$full_addr.'",
          "'.$is_active.'",
          "'.$phone_upr.'",
          "'.$phone_man.'",
          "'.$date_time_update.'"
        )
      ');
    }

    static function insert_point_sett_hist(int $point_id, int $creator_id, int $cafe_handle_close, int $cook_common_stol, string $summ_driver, string $summ_driver_min, int $priority_order, int $priority_pizza, int $rolls_pizza_dif, string $date_time_update): void
    {
      DB::insert(/** @lang text */ '
          INSERT INTO
            jaco_main_rolls.`points_settings_hist` (point_id, creator_id, cafe_handle_close, cook_common_stol, summ_driver, summ_driver_min, priority_order, priority_pizza, rolls_pizza_dif, date_time_update)
          VALUES (
            "'.$point_id.'",
            "'.$creator_id.'",
            "'.$cafe_handle_close.'",
            "'.$cook_common_stol.'",
            "'.$summ_driver.'",
            "'.$summ_driver_min.'",
            "'.$priority_order.'",
            "'.$priority_pizza.'",
            "'.$rolls_pizza_dif.'",
            "'.$date_time_update.'"
          )
        ');
    }

}
