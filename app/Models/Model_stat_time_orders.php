<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Model_stat_time_orders extends Model
{
    use HasFactory;

    static function get_unics_users(string $base, string $date): array
    {
      return DB::select(/** @lang text */'
        SELECT
          ua.`user_id`,
          IF( us.`short_name` IS NULL OR us.`short_name`="", us.`name`, us.`short_name` ) as user_name,
          IF( app.`short_name` IS NULL OR app.`short_name`="", app.`name`, app.`short_name` ) as app_name,
          app1.`name` as new_app_name,
          csd.`my_color_day`
        FROM
          '.$base.'.`user_all_time_work` ua
          LEFT JOIN jaco_main_rolls.`users` us
              ON
                  us.`id`=ua.`user_id`

          LEFT JOIN jaco_main_rolls.`cafe_smena_days` csd
              ON
              csd.`user_id`=ua.`user_id`
                  AND
              csd.`min`>0
                  AND
              csd.`smena_id`>0
                  AND
              csd.`date`="'.$date.'"
          LEFT JOIN jaco_main_rolls.`appointment` app1
              ON
              app1.`id`=csd.`new_app`
                  AND
              csd.`new_app`!=0
          LEFT JOIN jaco_main_rolls.`appointment` app
              ON
              app.`id`=csd.`app_id`
        WHERE
          ua.`date` = "'.$date.'"
              AND
          ua.`date`=csd.`date`
        GROUP BY
          ua.`user_id`
        ORDER BY
					app.`kind` ASC,
					app.`sort` ASC,
					IF( us.`short_name` IS NULL OR us.`short_name`="", us.`name`, us.`short_name` ) ASC
      ') ?? [];
    }

    static function get_unics_hours(string $base, string $date): array
    {
      return DB::select(/** @lang text */'
        SELECT
          ua.`h`,
          "0" as all_time_sec,
          "0" as povar_time_sec,
          "0" as kassir_time_sec,
          "0" as work_time_sec,
          "0" as pf_work_time_sec
        FROM
          '.$base.'.`user_all_time_work` ua
        WHERE
          ua.`date` = "'.$date.'"
        GROUP BY
          ua.`h`
        ORDER BY
          ua.`h` ASC
      ') ?? [];
    }

    static function get_unics_users_all_time(string $base, string $date, int $user_id): array
    {
      return DB::select(/** @lang text */'
        SELECT
          `user_id`,
          `h`,
          SUM(`time`) as time
        FROM
          '.$base.'.`user_all_time_work`
        WHERE
          `date` = "'.$date.'"
              AND
          `user_id`="'.$user_id.'"
        GROUP BY
          `h`
      ') ?? [];
    }

    static function get_unics_users_all_time_all(string $base, string $date, int $user_id): object|null
    {
      return DB::selectOne(/** @lang text */'
        SELECT
          SUM(`time`) as time
        FROM
          '.$base.'.`user_all_time_work`
        WHERE
          `date` = "'.$date.'"
              AND
          `user_id`="'.$user_id.'"
      ');
    }

    static function get_unics_users_povar_time(string $base, string $date, int $user_id): array
    {
      return DB::select(/** @lang text */'
        SELECT
          `user_id`,
          `h`,
          SUM(`time`) as time
        FROM
          '.$base.'.`user_all_time_work`
        WHERE
          `date` = "'.$date.'"
              AND
          `user_id`="'.$user_id.'"
              AND
          `type` IN (
              "user_stage_1",
              "user_stage_2",
              "user_mentor_stage_1",
              "user_mentor_stage_2",
              "user_stage_3_pizza",
              "user_mentor_stage_3_pizza")
        GROUP BY
          `h`
      ') ?? [];
    }

    static function get_unics_users_povar_time_all(string $base, string $date, int $user_id): object|null
    {
      return DB::selectOne(/** @lang text */'
        SELECT
          SUM(`time`) as time
        FROM
          '.$base.'.`user_all_time_work`
        WHERE
          `date` = "'.$date.'"
            AND
          `user_id`="'.$user_id.'"
            AND
          `type` IN (
            "user_stage_1",
            "user_stage_2",
            "user_mentor_stage_1",
            "user_mentor_stage_2",
            "user_stage_3_pizza",
            "user_mentor_stage_3_pizza")
      ');
    }

    static function get_unics_users_kassir_time(string $base, string $date, int $user_id): array
    {
      return DB::select(/** @lang text */'
        SELECT
          `user_id`,
          `h`,
          SUM(`time`) as time
        FROM
          '.$base.'.`user_all_time_work`
        WHERE
          `date` = "'.$date.'"
              AND
          `user_id`="'.$user_id.'"
              AND
          `type` IN (
              "kassir",
              "user_stage_3_rolls",
              "user_mentor_stage_3_rolls")
        GROUP BY
          `h`
      ') ?? [];
    }

    static function get_unics_users_kassir_time_all(string $base, string $date, int $user_id): object|null
    {
      return DB::selectOne(/** @lang text */'
        SELECT
          SUM(`time`) as time
        FROM
          '.$base.'.`user_all_time_work`
        WHERE
          `date` = "'.$date.'"
              AND
          `user_id`="'.$user_id.'"
              AND
          `type` IN (
              "kassir",
              "user_stage_3_rolls",
              "user_mentor_stage_3_rolls")
      ');
    }

    static function get_unics_users_work_time(string $base, string $date, int $user_id): array
    {
      return DB::select(/** @lang text */'
        SELECT
          `user_id`,
          `h`,
          SUM(`time`) as time
        FROM
          '.$base.'.`user_all_time_work`
        WHERE
          `date` = "'.$date.'"
              AND
          `user_id`="'.$user_id.'"
              AND
          `type` IN (
              "work",
              "work_mentor",
              "manager_work",
              "bill_work",
              "manager_cash")
        GROUP BY
            `h`
      ') ?? [];
    }

    static function get_unics_users_work_time_all(string $base, string $date, int $user_id): object|null
    {
      return DB::selectOne(/** @lang text */'
        SELECT
          SUM(`time`) as time
        FROM
          '.$base.'.`user_all_time_work`
        WHERE
          `date` = "'.$date.'"
              AND
          `user_id`="'.$user_id.'"
              AND
          `type` IN (
              "work",
              "work_mentor",
              "manager_work",
              "bill_work",
              "manager_cash")
      ');
    }

    static function get_unics_users_pf_work_time(string $base, string $date, int $user_id): array
    {
      return DB::select(/** @lang text */'
        SELECT
          `user_id`,
          `h`,
          SUM(`time`) as time
        FROM
          '.$base.'.`user_all_time_work`
        WHERE
          `date` = "'.$date.'"
              AND
          `user_id`="'.$user_id.'"
              AND
          `type` IN (
              "pf_work",
              "pf_work_mentor",
              "manager_pf_work")
        GROUP BY
            `h`
      ') ?? [];
    }

    static function get_unics_users_pf_work_time_all(string $base, string $date, int $user_id): object|null
    {
      return DB::selectOne(/** @lang text */'
        SELECT
          SUM(`time`) as time
        FROM
          '.$base.'.`user_all_time_work`
        WHERE
          `date` = "'.$date.'"
              AND
          `user_id`="'.$user_id.'"
              AND
          `type` IN (
              "pf_work",
              "pf_work_mentor",
              "manager_pf_work")
      ');
    }

    static function get_time_queue_orders(string $base, string $h): object|null
    {
      return DB::selectOne(/** @lang text */'
        SELECT
          `time_min`
        FROM
          '.$base.'.`time_queue_orders`
        WHERE
          `h`="'.$h.'"
      ');
    }

    static function convert_time(string $time): string
    {
      $sec = (int)((int)$time / 60);

      $hours = floor($sec / 60);
      $minutes = $sec % 60;

      $hours = (int)$hours > 9 ? $hours : '0'.$hours;
      $minutes = $minutes > 9 ? $minutes : '0'.$minutes;

      return $hours.':'.$minutes;
    }

}
