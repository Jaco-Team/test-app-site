<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Model_site_user_manager extends Model
{
    use HasFactory;

    static function check(int $app_id): object|null
    {
      return DB::selectOne(/** @lang text */'
        SELECT
          `is_graph`,
          `kind`
        FROM
          jaco_main_rolls.`appointment`
        WHERE
          `id`=:app_id
      ', ['app_id' => $app_id]);
    }

    static function get_all_apps(int $kind): array
    {
      return DB::select(/** @lang text */ '
        SELECT
          `id`,
          `name`,
          `is_graph`
        FROM
          jaco_main_rolls.`appointment`
        WHERE
          `kind`>:kind
        ORDER BY
          `sort`
      ', ['kind' => $kind]) ?? [];
    }

    static function get_specific_apps(): array
    {
      return DB::select(/** @lang text */ '
        SELECT
          `id`,
          `name`,
          `is_graph`
        FROM
          jaco_main_rolls.`appointment`
        WHERE
          `id` IN (18, 30, 10, 0)
        ORDER BY
          `sort`
      ') ?? [];
    }

    static function get_all_users(int $kind, int $app_id, int $point_id, string $check_app, string $check_name, int $id): array
    {
      return DB::select(/** @lang text */ '
        SELECT
          u.`id`,
          u.`name`,
          u.`login`,
          app.`name` as app_name,
          ui.`name` as img_name,
          ui.`date_update` as img_update
        FROM
          jaco_main_rolls.`users` u
          LEFT JOIN jaco_main_rolls.`user_privileges` up
            ON
              up.`user_id`=u.`id`
          LEFT JOIN jaco_main_rolls.`appointment` app
            ON
              app.`id`=up.`appointment_id`
          LEFT JOIN jaco_main_rolls.`users_images` ui
            ON
              u.id=ui.user_id
        WHERE
          app.`kind`>"'.$kind.'"

            AND

          (app.`id`="'.$app_id.'"
            OR
          ("'.$app_id.'"=-1
            AND
          app.`id`!=0))

            AND

          (up.`point_id`="'.$point_id.'"
            OR
          "'.$point_id.'"=-1
            OR
          "'.$app_id.'"=0)

            AND

          u.`id`!="'.$id.'"
          '.$check_app.'
          '.$check_name.'
        ORDER BY
          app.`sort`
      ') ?? [];
    }

    static function get_cities(int $city_id): array
    {
      return DB::select(/** @lang text */ '
        SELECT
          `id`,
          `name`
        FROM
          jaco_main_rolls.`cities`
        WHERE
          `is_show`=1
            AND
          (`id`="'.$city_id.'"
            OR
          "'.$city_id.'"="-1")
      ') ?? [];
    }

    static function get_one_user(int $user_id): object|null
    {
      return DB::selectOne(/** @lang text */'
        SELECT
					u.`id`,
					u.`name`,
					u.`auth_code`,
					u.`inn`,
					u.`acc_to_kas`,
					u.`login`,
					up.`city_id`,
					up.`point_id`,
					app.`id` as app_id,
					app.`name` as app_name,
					ui.`name` as img_name,
					ui.`date_update` as img_update,
					u.`birthday`
				FROM
					jaco_main_rolls.`users` u
					LEFT JOIN jaco_main_rolls.`user_privileges` up
						ON
							up.`user_id`=u.`id`
					LEFT JOIN jaco_main_rolls.`appointment` app
						ON
							app.`id`=up.`appointment_id`
					LEFT JOIN jaco_main_rolls.`users_images` ui
						ON
							u.`id`=ui.`user_id`
				WHERE
					u.`id`=:user_id
      ', ['user_id' => $user_id]);
    }

    static function get_history_one_user(int $user_id): array
    {
      return DB::select(/** @lang text */'
        SELECT
					uh.`name`,
					uh.`login`,
					uh.`date_time_update`,
					IF(ufh.`short_name`="", ufh.`name`, ufh.`short_name`) as update_name,
					uh.`auth_code`,
					uh.`acc_to_kas`,
					uh.`inn`,
					app.`name` as app_name,
					IF( uh.`city_id`="-1", "Все города", c.`name` ) as city_name,
					IF( uh.`point_id`="-1", "Все точки", p.`addr` ) as point_name
				FROM
					jaco_main_rolls.`users_history` uh
					LEFT JOIN jaco_main_rolls.`users` ufh
						ON
							ufh.`id`=uh.`update_user_id`
					LEFT JOIN jaco_main_rolls.`appointment` app
						ON
							app.`id`=uh.`app_id`
					LEFT JOIN jaco_main_rolls.`cities` c
						ON
							c.`id`=uh.`city_id`
					LEFT JOIN jaco_main_rolls.`points` p
						ON
							p.`id`=uh.`point_id`
				WHERE
					uh.`user_id`=:user_id
				ORDER BY
					uh.`date_time_update` DESC
      ', ['user_id' => $user_id]) ?? [];
    }

    static function get_user_phone_history(int $user_id): array
    {
      return DB::select(/** @lang text */ '
        SELECT
					*
				FROM
					jaco_main_rolls.`user_phone_history`
				WHERE
					`user_id`=:user_id
      ', ['user_id' => $user_id]) ?? [];
    }

    static function get_user_holidays(int $user_id): array
    {
      return DB::select(/** @lang text */ '
        SELECT
          *
        FROM
          jaco_main_rolls.`users_holidays`
        WHERE
          `user_id`=:user_id
      ', ['user_id' => $user_id]) ?? [];
    }

    static function my_mb_up_first(string $str): string
    {
      $fc = mb_strtoupper(mb_substr($str, 0, 1));
      return $fc.mb_substr($str, 1);
    }

    static function check_user_login(int $login): object|null
    {
      return DB::selectOne(/** @lang text */'
        SELECT
					u.*
				FROM
					jaco_main_rolls.`users` u
				WHERE
					u.`login`=:login
      ', ['login' => $login]);
    }

    static function check_user_auth_code(int $auth_code): object|null
    {
      return DB::selectOne(/** @lang text */'
        SELECT
          *
        FROM
          jaco_main_rolls.`users`
        WHERE
          `auth_code`=:auth_code
      ', ['auth_code' => $auth_code]);
    }

    static function insert_new_user(string $full_name, string $short_name, string $auth_code, string $inn, int $acc_to_kas, string $birthday, string $login, string $date, string $name, string $fam, string $otc): int
    {
      DB::insert(/** @lang text */ '
        INSERT INTO jaco_main_rolls.`users` (name, short_name, auth_code, inn, acc_to_kas, birthday, login, date_registration, user_name, user_fam, user_otch)
        VALUES(
          "'.$full_name.'",
          "'.$short_name.'",
          "'.$auth_code.'",
          "'.$inn.'",
          "'.$acc_to_kas.'",
          "'.$birthday.'",
          "'.$login.'",
          "'.$date.'",
          "'.$name.'",
          "'.$fam.'",
          "'.$otc.'"
        )
      ');

      return DB::getPdo()->lastInsertId();
    }

    static function insert_user_history(int $user_id, string $full_name, string $short_name, string $login, string $date_time_update, string $birthday, string $auth_code, int $acc_to_kas, int $is_show, string $inn, int $app_id, int $point_id, int $city_id, int $update_user_id, string $text_close): void
    {
      DB::insert(/** @lang text */ '
        INSERT INTO jaco_main_rolls.`users_history` (user_id, name, short_name, login, date_time_update, birthday, auth_code, acc_to_kas, is_show, inn, app_id, point_id, city_id, update_user_id, text_close)
        VALUES(
          "'.$user_id.'",
          "'.$full_name.'",
          "'.$short_name.'",
          "'.$login.'",
          "'.$date_time_update.'",
          "'.$birthday.'",
          "'.$auth_code.'",
          "'.$acc_to_kas.'",
          "'.$is_show.'",
          "'.$inn.'",
          "'.$app_id.'",
          "'.$point_id.'",
          "'.$city_id.'",
          "'.$update_user_id.'",
          "'.$text_close.'"
        )
      ');
    }

    static function insert_user_privileges(int $appointment_id, int $point_id, int $city_id, int $user_id): void
    {
      DB::insert(/** @lang text */ '
        INSERT INTO jaco_main_rolls.`user_privileges` (appointment_id, point_id, city_id, user_id)
        VALUES(
          "'.$appointment_id.'",
          "'.$point_id.'",
          "'.$city_id.'",
          "'.$user_id.'"
        )
      ');
    }

    static function check_user(int $user_id): object|null
    {
      return DB::selectOne(/** @lang text */'
        SELECT
					u.*,
					up.`appointment_id` as app_id
				FROM
					jaco_main_rolls.`users` u
					LEFT JOIN jaco_main_rolls.`user_privileges` up
						ON
							u.`id`=up.`user_id`
				WHERE
					u.`id`=:user_id
      ', ['user_id' => $user_id]);
    }

    static function get_user_app(int $user_id): object|null
    {
      return DB::selectOne(/** @lang text */'
        SELECT
					`appointment_id`
				FROM
					jaco_main_rolls.`user_privileges`
				WHERE
					`user_id`=:user_id
      ', ['user_id' => $user_id]);
    }

    static function insert_user_phone_history(int $user_id, string $date, string $old_phone, string $new_phone): void
    {
      DB::insert(/** @lang text */ '
        INSERT INTO jaco_main_rolls.`user_phone_history` (user_id, date, old_phone, new_phone)
        VALUES(
          "'.$user_id.'",
          "'.$date.'",
          "'.$old_phone.'",
          "'.$new_phone.'"
        )
      ');
    }

    static function update_user_login(string $login, int $user_id): void
    {
      DB::update(/** @lang text */'
        UPDATE
          jaco_main_rolls.`users`
        SET
          `login`="'.$login.'"
        WHERE
          `id`="'.$user_id.'"
      ');
    }

    static function update_user_date_del(string $date, int $user_id): void
    {
      DB::update(/** @lang text */'
        UPDATE
          jaco_main_rolls.`users`
        SET
          `date_del`="'.$date.'"
        WHERE
          `id`="'.$user_id.'"
      ');
    }

    static function insert_user_del_history(int $manager_id, string $date, string $text, int $user_id, string $user_phone): void
    {
      DB::insert(/** @lang text */ '
          INSERT INTO jaco_main_rolls.`users_del_history` (manager_id, date_time, text, user_id, user_phone)
          VALUES(
            "'.$manager_id.'",
            "'.$date.'",
            "'.$text.'",
            "'.$user_id.'",
            "'.$user_phone.'"
          )
        ');
    }

    static function update_user_data(string $full_name, string $short_name, string $user_name, string $user_fam, string $user_otc, string $auth_code, string $inn, int $acc_to_kas, string $birthday, string $login, int $user_id): void
    {
      DB::update(/** @lang text */'
        UPDATE
					jaco_main_rolls.`users`
				SET
					`name`="'.$full_name.'",
					`short_name`="'.$short_name.'",

					`user_name`="'.$user_name.'",
					`user_fam`="'.$user_fam.'",
					`user_otch`="'.$user_otc.'",

					`auth_code`="'.$auth_code.'",
					`inn`="'.$inn.'",
					`acc_to_kas`="'.$acc_to_kas.'",
					`birthday`="'.$birthday.'",
					`login`="'.$login.'"
				WHERE
					`id`="'.$user_id.'"
      ');
    }

    static function update_user_privileges(int $appointment_id, int $point_id, int $city_id, int $user_id): void
    {
      DB::update(/** @lang text */'
        UPDATE
          jaco_main_rolls.`user_privileges`
        SET
          `appointment_id`="'.$appointment_id.'",
          `point_id`="'.$point_id.'",
          `city_id`="'.$city_id.'"
        WHERE
          `user_id`="'.$user_id.'"
      ');
    }

    static function check_user_smena(int $old_app_id, int $user_id, string $this_date): object|null
    {
      return DB::selectOne(/** @lang text */'
        SELECT
          *
        FROM
          jaco_main_rolls.`cafe_smena_info`
        WHERE
          `app_id`=:old_app_id
            AND
          `user_id`=:user_id
            AND
          `date`=:this_date
      ', ['old_app_id' => $old_app_id, 'user_id' => $user_id, 'this_date' => $this_date]);
    }

    static function update_user_app_data(int $check_user_smena, int $old_app_id, int $user_id, string $max_day): void
    {
      DB::update(/** @lang text */'
        UPDATE
          jaco_main_rolls.`cafe_smena_info`
        SET
          `smena_id`="'.$check_user_smena.'"
        WHERE
          `app_id`="'.$old_app_id.'"
            AND
          `user_id`="'.$user_id.'"
            AND
          `date`>"'.$max_day.'"
      ');
    }

    static function insert_user_cafe_smena_event(int $date_time, string $check_user_smena, int $user_id): void
    {
      DB::insert(/** @lang text */ '
        INSERT INTO jaco_main_rolls.`cafe_smena_event` (date_time, event, user_id, from_user_id)
        VALUES(
          "'.$date_time.'"
          "updateGraph_500_'.$check_user_smena.'_",
          "-55",
          "'.$user_id.'"
        )
      ');
    }

    static function update_user_cafe_smena_days(int $app_id, int $old_app_id, int $user_id, string $date): void
    {
      DB::update(/** @lang text */'
        UPDATE
          jaco_main_rolls.`cafe_smena_days`
        SET
          `app_id`="'.$app_id.'"
        WHERE
          `app_id`="'.$old_app_id.'"
            AND
          `user_id`="'.$user_id.'"
            AND
          `date`>"'.$date.'"
            AND
          `smena_id`!=0
      ');
    }

    static function update_user_cafe_smena_hours(int $app_id, int $old_app_id, int $user_id, string $date): void
    {
      DB::update(/** @lang text */'
        UPDATE
          jaco_main_rolls.`cafe_smena_hours`
        SET
          `app_id`="'.$app_id.'"
        WHERE
          `app_id`="'.$old_app_id.'"
            AND
          `user_id`="'.$user_id.'"
            AND
          `date`>"'.$date.'"
      ');
    }

    static function check_user_smena_info(string $this_date, int $check_user_smena, int $user_id, int $app_id): object|null
    {
      return DB::selectOne(/** @lang text */'
        SELECT
            *
          FROM
            jaco_main_rolls.`cafe_smena_info`
          WHERE
            `date`=:this_date
              AND
            `smena_id`=:check_user_smena
              AND
            `user_id`=:user_id
              AND
            `app_id`=:app_id
      ', ['check_user_smena' => $check_user_smena, 'user_id' => $user_id, 'this_date' => $this_date, 'app_id' => $app_id]);
    }

    static function update_povar_cafe_smena_days(int $user_id, string $this_date, string $max_day): void
    {
      DB::update(/** @lang text */'
        UPDATE
          jaco_main_rolls.`cafe_smena_days`
        SET
          `new_app`=5
        WHERE
          `app_id` IN (12, 22)
            AND
          `new_app`=0
            AND
          `user_id`="'.$user_id.'"
            AND
          `date` BETWEEN "'.$this_date.'" AND "'.$max_day.'"
      ');
    }

    static function update_kassir_cafe_smena_days(int $user_id, string $this_date, string $max_day): void
    {
      DB::update(/** @lang text */'
        UPDATE
          jaco_main_rolls.`cafe_smena_days`
        SET
          `new_app`=6
        WHERE
          `app_id` IN (23)
            AND
          `new_app`=0
            AND
          `user_id`="'.$user_id.'"
            AND
          `date` BETWEEN "'.$this_date.'" AND "'.$max_day.'"
      ');
    }

    static function update_intern_cafe_smena_days(int $user_id, string $this_date, string $max_day): void
    {
      DB::update(/** @lang text */'
        UPDATE
          jaco_main_rolls.`cafe_smena_days`
        SET
          `new_app`=21
        WHERE
          `app_id` IN (24)
            AND
          `new_app`=0
            AND
          `user_id`="'.$user_id.'"
            AND
          `date` BETWEEN "'.$this_date.'" AND "'.$max_day.'"
      ');
    }
    static function update_user_data_change(string $next_per, int $app_id, int $point_id, int $city_id, int $user_id): void
    {
      DB::update(/** @lang text */'
         UPDATE
            jaco_main_rolls.`users`
          SET
            `date_change`="'.$next_per.'",
            `app_change`="'.$app_id.'",
            `point_change`="'.$point_id.'",
            `city_change`="'.$city_id.'"
          WHERE
            `id`="'.$user_id.'"
      ');
    }

    static function check_next_per(int $old_app_id, int $user_id, string $max_day): array
    {
      return DB::select(/** @lang text */ '
        SELECT
          *
        FROM
          jaco_main_rolls.`cafe_smena_days`
        WHERE
          `app_id`=:old_app_id
            AND
          `user_id`=:user_id
            AND
          `date`>:max_day
      ', ['old_app_id' => $old_app_id, 'user_id' => $user_id, 'max_day' => $max_day]) ?? [];
    }

    static function update_user_old_app(int $app_id, int $old_app_id, int $user_id, string $max_day): void
    {
      DB::update(/** @lang text */'
        UPDATE
          jaco_main_rolls.`cafe_smena_info`
        SET
          `app_id`="'.$app_id.'",
          `min_price`=0,
          `avg_price`=0,
          `max_price`=0,
          `max_bonus`=0,
          `my_price`=0
        WHERE
          `app_id`="'.$old_app_id.'"
            AND
          `user_id`="'.$user_id.'"
            AND
          `date`>"'.$max_day.'"
      ');
    }

    static function update_user_cafe_smena_days_2(int $app_id, int $old_app_id, int $user_id, string $date): void
    {
      DB::update(/** @lang text */'
        UPDATE
          jaco_main_rolls.`cafe_smena_days`
        SET
          `app_id`="'.$app_id.'",
          `new_app`=0
        WHERE
          `app_id`="'.$old_app_id.'"
            AND
          `user_id`="'.$user_id.'"
            AND
          `date`>"'.$date.'"
      ');
    }

}
