<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Model_app_work extends Model
{
    use HasFactory;

    static function get_app_work_items(): array
    {
      return DB::select(/** @lang text */ '
        SELECT
          aw.`id`,
          app.`name` as app_name,
          aw.`name` as work_name,
          CONCAT(aw.`name`, " ", app.`name`) as name,
          aw.`time_min`,
          aw.`type`,
          aw.`text`,
          aw.`is_active`,
          aw.`dow`,
          aw.`is_not_del`,
          aw.`is_need`,
          ( SELECT GROUP_CONCAT(awt.`time_action`) FROM jaco_main_rolls.`app_work_time` awt WHERE awt.`work_id`=aw.`id` AND awt.`type_action`=1 ) as times_open,
          ( SELECT GROUP_CONCAT(awt.`time_action`) FROM jaco_main_rolls.`app_work_time` awt WHERE awt.`work_id`=aw.`id` AND awt.`type_action`=2 ) as times_close
        FROM
          jaco_main_rolls.`app_work` aw
          LEFT JOIN jaco_main_rolls.`appointment` app
          ON
          app.`id`=aw.`app_id`
        ORDER BY
          app.`sort`,
          aw.`dow`,
          aw.`id`
        ') ?? [];
    }

    static function get_app_work_items_min(): array
    {
      return DB::select(/** @lang text */ '
        SELECT
          aw.`id`,
          CONCAT(aw.`name`, " ", app.`name`) as name
        FROM
          jaco_main_rolls.`app_work` aw
          LEFT JOIN jaco_main_rolls.`appointment` app
              ON
            app.`id`=aw.`app_id`
        ORDER BY
          app.`sort`,
          aw.`dow`,
          aw.`id`
      ') ?? [];
    }

    static function get_this_item(int $item_id): object
    {
      return DB::selectOne(/** @lang text */'
        SELECT
          *
        FROM
          jaco_main_rolls.`app_work`
        WHERE
          `id`=:item_id
      ', ['item_id' => $item_id]);
    }

    static function get_apps(): array
    {
      return DB::select(/** @lang text */ '
        SELECT
          `id`,
          `name`
        FROM
          jaco_main_rolls.`appointment`
        WHERE
          `type` IS NOT NULL
             AND
          `is_show`=1
            AND
          `id` IN (5, 6, 21)
        ORDER BY
          `sort`
      ') ?? [];
    }

    static function get_times_add(int $item_id): array
    {
      return DB::select(/** @lang text */'
        SELECT
          *
        FROM
          jaco_main_rolls.`app_work_time`
        WHERE
          `type_action`=1
              AND
          `work_id`=:item_id
        ORDER BY
          `time_action`
      ', ['item_id' => $item_id]) ?? [];
    }

    static function get_times_close(int $item_id): object|null
    {
      return DB::selectOne(/** @lang text */'
        SELECT
          `time_action`
        FROM
          jaco_main_rolls.`app_work_time`
        WHERE
          `type_action`=2
            AND
          `work_id`=:item_id
        ORDER BY
          `time_action`
      ', ['item_id' => $item_id]);
    }

    static function get_cats(): array
    {
      return DB::select(/** @lang text */ '
        SELECT
          `id`,
          `name`
        FROM
          jaco_main_rolls.`cat_work`
        ORDER BY
          `name`
      ') ?? [];
    }

    static function save_check_item(string $type, int $item_id, string $value): bool
    {
      return DB::update(/** @lang text */'
        UPDATE
          jaco_main_rolls.`app_work`
        SET
          '.$type.'='.$value.'
        WHERE
          `id`='.$item_id.'
      ');
    }

    static function check_new_item(int $item_id, string $name): object|null
    {
      return DB::selectOne(/** @lang text */'
        SELECT
          *
        FROM
          jaco_main_rolls.`app_work`
        WHERE
          `app_id`=:item_id
              AND
          `name`=:name
        ', ['item_id' => $item_id, 'name' => $name]
      );
    }

    static function insert_new_item(int $app_id, string $name, int $dow, int $type_new, int $time_min, int $time_sec, string $description, int $type_time, int $max_count, int $work_id): int
    {
      DB::insert(/** @lang text */ '
        INSERT INTO jaco_main_rolls.`app_work` (app_id, name, dow, type_new, time_min, time_sec, description, type_time, max_count, work_id)
        VALUES(
          "'.$app_id.'",
          "'.$name.'",
          "'.$dow.'",
          "'.$type_new.'",
          "'.$time_min.'",
          "'.$time_sec.'",
          "'.$description.'",
          "'.$type_time.'",
          "'.$max_count.'",
          "'.$work_id.'"
        )
      ');

      return DB::getPdo()->lastInsertId();
    }

    static function insert_times_item(int $work_id, string $time_action, int $type_action)
    {
      DB::insert(/** @lang text */ '
          INSERT INTO jaco_main_rolls.`app_work_time` (work_id, time_action, type_action)
          VALUES(
              :work_id,
              :time_action,
              :type_action
          )
        ', ['work_id' => $work_id, 'time_action' => $time_action, 'type_action' => $type_action]);
    }

    static function update_edit_item(int $item_id, int $app_id, string $name, int $dow, int $type_new, int $time_min, int $time_sec, string $description, int $type_time, int $max_count, int $work_id)
    {
      return DB::update(/** @lang text */'
          UPDATE
            jaco_main_rolls.`app_work`
          SET
            `name`="'.$name.'",
            `app_id`="'.$app_id.'",
            `dow`="'.$dow.'",
            `time_min`="'.$time_min.'",
            `time_sec`="'.$time_sec.'",
            `type_time`="'.$type_time.'",
            `max_count`="'.$max_count.'",
            `type_new`="'.$type_new.'",
            `description`="'.$description.'",
            `work_id`="'.$work_id.'"
          WHERE
            `id`="'.$item_id.'"
        ');
    }

    static function delete_times_item(int $item_id)
    {
      DB::delete(/** @lang text */ '
          DELETE FROM
            jaco_main_rolls.`app_work_time`
          WHERE
            `work_id`=:item_id
      ', ['item_id' => $item_id]);
    }
}
