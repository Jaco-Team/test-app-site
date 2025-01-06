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
					CONCAT(pz.name, " ( зона ", pz.id, " ) ") as zone_name,
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

    static function insert_event_new_hist(int $user_id, string $type_active, string $type, int $point_id, int $zone_id, string $this_date, string $every, string $comment): void
    {
      DB::insert(/** @lang text */ '
        INSERT INTO
          jaco_site_rolls.`event_new_hist` (user_id, type_active, type, point_id, zone_id, date, every, comment)
        VALUES (
          "'.$user_id.'",
          "'.$type_active.'",
          "'.$type.'",
          "'.$point_id.'",
          "'.$zone_id.'",
          "'.$this_date.'",
          "'.$every.'",
          "'.$comment.'"
        )
      ');
    }

    static function insert_event_new(string $type, int $point_id, int $zone_id, string $this_date, string $every, string $comment): void
    {
      DB::insert(/** @lang text */ '
        INSERT INTO
          jaco_site_rolls.`events_new` (type, point_id, zone_id, date, every, comment)
        VALUES (
          "'.$type.'",
          "'.$point_id.'",
          "'.$zone_id.'",
          "'.$this_date.'",
          "'.$every.'",
          "'.$comment.'"
        )
      ');
    }

    static function insert_cafe_close_history(int $user_id_close, int $point_id, string $this_date, string $comment): void
    {
      DB::insert(/** @lang text */ '
        INSERT INTO
          jaco_site_rolls.`cafe_close_history` (user_id_close, point_id, date_time_close, comment)
        VALUES (
          "'.$user_id_close.'",
          "'.$point_id.'",
          "'.$this_date.'",
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

    static function get_point_info_hist(int $point_id): array
    {
      return DB::select(/** @lang text */ '
        SELECT
          pih.*,
          (SELECT `name` FROM jaco_main_rolls.`users` u WHERE u.`id` = pih.`creator_id` ) as user_name
        FROM
          jaco_main_rolls.`points_info_hist` pih
        WHERE
          pih.`point_id` = :point_id
        ORDER BY
	        pih.`date_time_update` ASC
      ', ['point_id' => $point_id]) ?? [];
    }

    static function get_point_rate_hist(int $point_id): array
    {
      return DB::select(/** @lang text */ '
        SELECT
          pih.*,
          (SELECT `name` FROM jaco_main_rolls.`users` u WHERE u.`id` = pih.`creator_id` ) as user_name
        FROM
          jaco_main_rolls.`points_rate_hist` pih
        WHERE
          pih.`point_id` = :point_id
        ORDER BY
          pih.`date_time_update` ASC
      ', ['point_id' => $point_id]) ?? [];
    }

    static function get_point_pay_hist(int $point_id): array
    {
      return DB::select(/** @lang text */ '
        SELECT
          pih.*,
          (SELECT `name` FROM jaco_main_rolls.`users` u WHERE u.`id` = pih.`creator_id` ) as user_name
        FROM
          jaco_main_rolls.`points_pay_hist` pih
        WHERE
          pih.`point_id` = :point_id
        ORDER BY
          pih.`date_time_update` ASC
      ', ['point_id' => $point_id]) ?? [];
    }

    static function get_point_sett_hist(int $point_id): array
    {
      return DB::select(/** @lang text */ '
        SELECT
          pih.*,
          (SELECT `name` FROM jaco_main_rolls.`users` u WHERE u.`id` = pih.`creator_id` ) as user_name
        FROM
          jaco_main_rolls.`points_settings_hist` pih
        WHERE
          pih.`point_id` = :point_id
        ORDER BY
          pih.`date_time_update` ASC
      ', ['point_id' => $point_id]) ?? [];
    }

    static function insert_new_point(int $city_id, string $addr, string $xy_point): false|string
    {
      DB::insert(/** @lang text */ '
        INSERT INTO
          jaco_main_rolls.`points` (city_id, addr, xy_point)
        VALUES (
          "'.$city_id.'",
          "'.$addr.'",
          "'.$xy_point.'"
        )
      ');

      return DB::getPdo()->lastInsertId();
    }

    // для тестов получить все не активные точки
    static function get_points_none_active(): array
    {
      return DB::select(/** @lang text */ '
        SELECT
          p.`base`,
          CONCAT(c.`name`, ", ", p.`addr`) as name,
          p.`id` as id,
          p.`city_id`
        FROM
          jaco_main_rolls.`points` p
          LEFT JOIN jaco_main_rolls.`cities` c
            ON
              c.`id`=p.`city_id`
        WHERE
          p.`is_active`=0
        ORDER BY
          p.`city_id`
     ') ?? [];
    }

    static function get_name_zone(int $zone_id): object
    {
      return DB::selectOne(/** @lang text */ '
        SELECT
          `name`
        FROM
          jaco_main_rolls.`points_zone`
        WHERE
          `id`=:zone_id
      ', ['zone_id' => $zone_id]);
    }

    static function get_one_events_new(int $zone_id, string $date): object|null
    {
      return DB::selectOne(/** @lang text */ '
        SELECT
          *
        FROM
          jaco_site_rolls.`events_new`
        WHERE
          `date` = "'.$date.'"
            AND
          `zone_id` = "'.$zone_id.'"
        ORDER BY
          `id` DESC
      ');
    }

    static function delete_event_new(int $zone_id, string $date, int $type): void
    {
      DB::delete(/** @lang text */ '
        DELETE FROM
          jaco_site_rolls.`events_new`
        WHERE
         `date`="'.$date.'"
            AND
         `zone_id`="'.$zone_id.'"
            AND
         `type`="'.$type.'"
      ');
    }

    static function insert_point_zone(int $point_id, int $zone_id, int $creator_id, int $is_active, string $date_time_update): string|false
    {
      DB::insert(/** @lang text */ '
        INSERT INTO
          jaco_main_rolls.`points_zone_hist` (point_id, zone_id, creator_id, is_active, date_time_update)
        VALUES (
          "'.$point_id.'",
          "'.$zone_id.'",
          "'.$creator_id.'",
          "'.$is_active.'",
          "'.$date_time_update.'"
        )
      ');

      return DB::getPdo()->lastInsertId();
    }

    static function get_active_zone(int $zone_id, string $date): object|null
    {
      return DB::selectOne(/** @lang text */ '
        SELECT
          `id`
        FROM
          jaco_site_rolls.`events_new`
        WHERE
          `date` = "'.$date.'"
            AND
          `zone_id` = "'.$zone_id.'"
            AND
          `type` = 5
        ORDER BY
          `id` DESC
      ');
    }

    static function get_point_zone_hist(int $point_id): array
    {
      return DB::select(/** @lang text */ '
        SELECT
          pih.*,
          CONCAT(pz.`name`, " ( зона ", pz.`id`, " )") as zone_name,
          (SELECT `name` FROM jaco_main_rolls.`users` u WHERE u.`id` = pih.`creator_id` ) as user_name
        FROM
          jaco_main_rolls.`points_zone_hist` pih
          LEFT JOIN jaco_main_rolls.`points_zone` pz
						ON
          pz.`id`=pih.`zone_id`
        WHERE
          pih.`point_id` = :point_id
        ORDER BY
          pih.`date_time_update` ASC
      ', ['point_id' => $point_id]) ?? [];
    }

    static function get_acces(int $app_id, int $module_id): array
    {
      return DB::select(/** @lang text */ '
        SELECT
          ag.`param`,
          atg.`value`
        FROM
          jaco_main_rolls.`appointment_group` ag
          LEFT JOIN jaco_main_rolls.`appointment_template_group` atg
            ON
              atg.`group_id`=ag.`id`
        WHERE
          atg.`appointment_id`=:app_id
            AND
          ag.`module_id`=:module_id
      ', ['app_id' => $app_id, 'module_id' => $module_id]) ?? [];
    }

}
