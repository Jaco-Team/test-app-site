<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Model_sklad_items_module extends Model
{
    use HasFactory;

    static function get_all_items(): array
    {
      return DB::select(/** @lang text */ '
        SELECT
          it.`id`,
          it.`name`,
          pf.`name` pf_name,
          ei.`name` ei_name,
          it.`percent`,
          it.`los_percent`,
          it.`show_in_rev`,
          it.`cat_id`,
          GROUP_CONCAT(DISTINCT st.`name` SEPARATOR ", ") as storage_name,
          it.`is_show`,
          it.`handle_price`,
          it.`art`,
          it.`show_in_order`
        FROM
          jaco_main_rolls.`items` it
          LEFT JOIN jaco_main_rolls.`polufabricat` pf
            ON
              it.`pf_id`=pf.`id`
          LEFT JOIN jaco_main_rolls.`ed_izmer` ei
            ON
              it.`ed_izmer_id`=ei.`id`
          LEFT JOIN jaco_main_rolls.`sklad_storage_items` ssp
            ON
              ssp.`item_id`=it.`id`
          LEFT JOIN jaco_main_rolls.`sklad_storage` st
            ON
              ssp.`storage_id`=st.`id`
        GROUP BY it.`id`
        ORDER BY it.`sort` ASC
      ') ?? [];
    }

    static function get_all_items_free(): array
    {
      return DB::select(/** @lang text */ '
        SELECT
          it.`id`,
          it.`name`,
          pf.`name` pf_name,
          ei.`name` ei_name,
          it.`percent`,
          it.`los_percent`,
          it.`show_in_rev`,
          it.`cat_id`,
          GROUP_CONCAT(DISTINCT st.`name` SEPARATOR ", ") as storage_name,
          it.`is_show`,
          it.`handle_price`,
          it.`art`,
          it.`show_in_order`
        FROM
          jaco_main_rolls.`items` it
          LEFT JOIN jaco_main_rolls.`polufabricat` pf
            ON
              it.`pf_id`=pf.`id`
          LEFT JOIN jaco_main_rolls.`ed_izmer` ei
            ON
              it.`ed_izmer_id`=ei.`id`
          LEFT JOIN jaco_main_rolls.`sklad_storage_items` ssp
            ON
              ssp.`item_id`=it.`id`
          LEFT JOIN jaco_main_rolls.`sklad_storage` st
            ON
              ssp.`storage_id`=st.`id`
        WHERE
          it.`cat_id`="0"
        GROUP BY it.`id`
        ORDER BY it.`sort` ASC
      ') ?? [];
    }

    static function get_main_cats(): array
    {
      return DB::select(/** @lang text */ '
        SELECT
          `id`,
          `name`
        FROM
          jaco_main_rolls.`items_cat`
        WHERE
          `parent_id`=-1
      ') ?? [];
    }

    static function get_item_cats(int $cat_id): array
    {
      return DB::select(/** @lang text */ '
        SELECT
          `id`,
          `name`
        FROM
          jaco_main_rolls.`items_cat`
        WHERE
          `parent_id`= :cat_id
      ', ['cat_id' => $cat_id]) ?? [];
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
          `id` IN (5, 6, 21)
      ') ?? [];
    }

    static function get_cats(): array
    {
      return DB::select(/** @lang text */ '
        SELECT
          `id`,
          `name`
        FROM
          jaco_main_rolls.`items_cat`
        WHERE
          `parent_id`!=-1
        ORDER BY
          `name` ASC
      ') ?? [];
    }

    static function get_allergens(): array
    {
      return DB::select(/** @lang text */ '
        SELECT
          `id`,
          `name`
        FROM
          jaco_site_rolls.`allergens`
        ORDER BY
          `name` ASC
      ') ?? [];
    }

    static function get_ed_izmer(): array
    {
      return DB::select(/** @lang text */ '
        SELECT
          `id`,
          `name`
        FROM
          jaco_main_rolls.`ed_izmer`
      ') ?? [];
    }

    static function get_pf_list_is_show(): array
    {
      return DB::select(/** @lang text */ '
        SELECT
          `id`,
          `name`
        FROM
          jaco_main_rolls.`polufabricat`
        WHERE
          `is_show`=1
      ') ?? [];
    }

    static function get_pf_list(): array
    {
      return DB::select(/** @lang text */ '
        SELECT
          `id`,
          `name`
        FROM
          jaco_main_rolls.`polufabricat`
      ') ?? [];
    }

    static function get_sklad_storage(): array
    {
      return DB::select(/** @lang text */ '
        SELECT
          `id`,
          `name`
        FROM
          jaco_main_rolls.`sklad_storage`
        ORDER BY
          `sort` ASC
      ') ?? [];
    }

    static function get_all_items_search(string $name): array
    {
      return DB::select(/** @lang text */ '
        SELECT
          it.`id`,
          it.`name`,
          pf.`name` pf_name,
          ei.`name` ei_name,
          it.`percent`,
          it.`los_percent`,
          it.`show_in_rev`,
          it.`cat_id`,
          GROUP_CONCAT(DISTINCT st.`name` SEPARATOR ", ") as storage_name,
          it.`is_show`,
          it.`handle_price`,
          it.`art`,
          it.`show_in_order`
        FROM
          jaco_main_rolls.`items` it
          LEFT JOIN jaco_main_rolls.`polufabricat` pf
            ON
              it.`pf_id`=pf.`id`
          LEFT JOIN jaco_main_rolls.`ed_izmer` ei
            ON
              it.`ed_izmer_id`=ei.`id`
          LEFT JOIN jaco_main_rolls.`sklad_storage_items` ssp
            ON
              ssp.`item_id`=it.`id`
          LEFT JOIN jaco_main_rolls.`sklad_storage` st
            ON
              ssp.`storage_id`=st.`id`
        WHERE
          it.`name` LIKE "%'.$name.'%"
        GROUP BY
          it.`id`
        ORDER BY
          it.`sort` ASC
      ') ?? [];
    }

    static function get_all_items_free_search(string $name): array
    {
      return DB::select(/** @lang text */ '
        SELECT
          it.`id`,
          it.`name`,
          pf.`name` pf_name,
          ei.`name` ei_name,
          it.`percent`,
          it.`los_percent`,
          it.`show_in_rev`,
          it.`cat_id`,
          GROUP_CONCAT(DISTINCT st.`name` SEPARATOR ", ") as storage_name,
          it.`is_show`,
          it.`handle_price`,
          it.`art`,
          it.`show_in_order`
        FROM
          jaco_main_rolls.`items` it
          LEFT JOIN jaco_main_rolls.`polufabricat` pf
            ON
              it.`pf_id`=pf.`id`
          LEFT JOIN jaco_main_rolls.`ed_izmer` ei
            ON
              it.`ed_izmer_id`=ei.`id`
          LEFT JOIN jaco_main_rolls.`sklad_storage_items` ssp
            ON
              ssp.`item_id`=it.`id`
          LEFT JOIN jaco_main_rolls.`sklad_storage` st
            ON
              ssp.`storage_id`=st.`id`
        WHERE
          it.`cat_id`="0"
            AND
          it.`name` LIKE "%'.$name.'%"
        GROUP BY it.`id`
        ORDER BY it.`sort` ASC
      ') ?? [];
    }

    static function get_item_ed_izmer(int $item_id): array
    {
      return DB::select(/** @lang text */ '
        SELECT
          ei.*
        FROM
          jaco_main_rolls.`items` i
          LEFT JOIN jaco_main_rolls.`polufabricat` pf
            ON
              pf.`id`=i.`pf_id`
          LEFT JOIN jaco_main_rolls.`ed_izmer` ei
            ON
              ei.`con_id`=pf.`ed_izmer_id`
        WHERE
          i.`id`=:item_id
      ', ['item_id' => $item_id]) ?? [];
    }

    static function get_one_item(int $item_id): object
    {
      return DB::selectOne(/** @lang text */ '
        SELECT
          *
        FROM
          jaco_main_rolls.`items`
        WHERE
          `id`=:item_id
      ', ['item_id' => $item_id]);
    }

    static function get_item_storages(int $item_id): array
    {
      return DB::select(/** @lang text */ '
       SELECT
        ss.`id` as id,
        ss.`name` as name
       FROM
        jaco_main_rolls.`sklad_storage` ss
        LEFT JOIN jaco_main_rolls.`sklad_storage_items` ssp
          ON
            ss.`id`=ssp.`storage_id`
       WHERE
        ssp.`item_id`=:item_id
       ORDER BY
        `sort` ASC
      ', ['item_id' => $item_id]) ?? [];
    }

    static function get_item_allergens(int $item_id): array
    {
      return DB::select(/** @lang text */ '
       SELECT
          al.`id` as id,
          al.`name` as name
        FROM
          jaco_site_rolls.`allergens` al
        LEFT JOIN jaco_main_rolls.`items_allergens` ia
            ON
           al.`id`= ia.`all_id`
        WHERE
          ia.`item_id`=:item_id
        ORDER BY
          `name` ASC
      ', ['item_id' => $item_id]) ?? [];
    }

    static function get_item_allergens_other(int $item_id): array
    {
      return DB::select(/** @lang text */ '
        SELECT
          al.`id` as id,
          al.`name` as name
        FROM
          jaco_site_rolls.`allergens` al
        LEFT JOIN jaco_main_rolls.`items_allergens_other` iao
            ON
              al.`id`= iao.`all_id`
        WHERE
          iao.`item_id`=:item_id
        ORDER BY
          `name` ASC
      ', ['item_id' => $item_id]) ?? [];
    }

    static function update_item(string $type, int $value, int $item_id): int
    {
      return DB::update(/** @lang text */'
        UPDATE
          jaco_main_rolls.`items`
        SET
          `'.$type.'`="'.$value.'"
        WHERE
          `id`="'.$item_id.'"
      ');
    }

    static function insert_item_hist(int $user_id, string $date, $item_id): int
    {
      DB::insert(/** @lang text */ '
        INSERT INTO jaco_main_rolls.`items_hist`
          (date_start, creator_id, item_id, name, name_for_vendor, storage_id_del, cat_id, sort, pf_id, ed_izmer_id, los_percent, pq, percent, vend_percent, art, time_min, time_sec, time_dop_min, time_dop_sec, time_min_other, time_sec_other, app_id, w_pf, w_trash, w_item, two_user, is_show, show_in_rev, show_in_order, handle_price, max_count_in_m, is_main, date_update, min_count)
        SELECT
          "'.$date.'",
          "'.$user_id.'",
          `id`,
          `name`,
          `name_for_vendor`,
          `storage_id_del`,
          `cat_id`,
          `sort`,
          `pf_id`,
          `ed_izmer_id`,
          `los_percent`,
          `pq`,
          `percent`,
          `vend_percent`,
          `art`,
          `time_min`,
          `time_sec`,
          `time_dop_min`,
          `time_dop_sec`,
          `time_min_other`,
          `time_sec_other`,
          `app_id`,
          `w_pf`,
          `w_trash`,
          `w_item`,
          `two_user`,
          `is_show`,
          `show_in_rev`,
          `show_in_order`,
          `handle_price`,
          `max_count_in_m`,
          `is_main`,
          `date_update`,
          `min_count`
        FROM
          jaco_main_rolls.`items`
        WHERE `id`="'.$item_id.'"
     ');

      return DB::getPdo()->lastInsertId();
    }

    static function insert_sklad_storage_items_hist(int $hist_id, int $storage_id, int $item_id): void
    {
      DB::insert(/** @lang text */ '
        INSERT INTO jaco_main_rolls.`sklad_storage_items_hist` (item_hist_id, storage_id, item_id)
          VALUES(
            "'.$hist_id.'",
            "'.$storage_id.'",
            "'.$item_id.'"
          )
      ');
    }

    static function insert_items_allergens_hist(int $hist_id, int $allergen_id, int $item_id): void
    {
      DB::insert(/** @lang text */ '
        INSERT INTO jaco_main_rolls.`items_allergens_hist` (item_hist_id, allergen_id, item_id)
          VALUES(
            "'.$hist_id.'",
            "'.$allergen_id.'",
            "'.$item_id.'"
          )
      ');
    }

    static function insert_items_allergens_other_hist(int $hist_id, int $allergen_id, int $item_id): void
    {
      DB::insert(/** @lang text */ '
        INSERT INTO jaco_main_rolls.`items_allergens_other_hist` (item_hist_id, allergen_id, item_id)
          VALUES(
            "'.$hist_id.'",
            "'.$allergen_id.'",
            "'.$item_id.'"
          )
      ');
    }

    static function get_item_hist(int $item_id): array
    {
      return DB::select(/** @lang text */ '
       SELECT
          *
        FROM
          jaco_main_rolls.`items_hist`
        WHERE
          `item_id`=:item_id
        ORDER BY
          `id` ASC
      ', ['item_id' => $item_id]) ?? [];
    }

    static function get_sklad_storage_items_hist(int $item_id, int $hist_id): array
    {
      return DB::select(/** @lang text */ '
       SELECT
          ss.`name`
        FROM
          jaco_main_rolls.`sklad_storage` ss
          LEFT JOIN jaco_main_rolls.`sklad_storage_items_hist` ssi
            ON
              ss.`id`=ssi.`storage_id`
        WHERE
          ssi.`item_id`=:item_id
            AND
          ssi.`item_hist_id`=:hist_id
        ORDER BY
          `sort` ASC
      ', ['item_id' => $item_id, 'hist_id' => $hist_id]) ?? [];
    }

    static function get_items_allergens_hist(int $item_id, int $hist_id): array
    {
      return DB::select(/** @lang text */ '
       SELECT
          al.`name`
        FROM
          jaco_site_rolls.`allergens` al
          LEFT JOIN jaco_main_rolls.`items_allergens_hist` iah
            ON
              al.`id`=iah.`allergen_id`
        WHERE
          iah.`item_id`=:item_id
            AND
          iah.`item_hist_id`=:hist_id
        ORDER BY
          al.`id` DESC
      ', ['item_id' => $item_id, 'hist_id' => $hist_id]) ?? [];
    }

    static function get_items_allergens_other_hist(int $item_id, int $hist_id): array
    {
      return DB::select(/** @lang text */ '
       SELECT
          al.`name`
        FROM
          jaco_site_rolls.`allergens` al
          LEFT JOIN jaco_main_rolls.`items_allergens_other_hist` iah
            ON
              al.`id`=iah.`allergen_id`
        WHERE
          iah.`item_id`=:item_id
            AND
          iah.`item_hist_id`=:hist_id
        ORDER BY
          al.`id` DESC
      ', ['item_id' => $item_id, 'hist_id' => $hist_id]) ?? [];
    }

    static function get_item_user_hist(int $creator_id): object
    {
      return DB::selectOne(/** @lang text */ '
        SELECT
          `name`
        FROM
          jaco_main_rolls.`users`
        WHERE
          `id`=:creator_id
      ', ['creator_id' => $creator_id]);
    }

    static function get_one_art(int $item_id, string $art): object|null
    {
      return DB::selectOne(/** @lang text */ '
        SELECT
          `id`
        FROM
          jaco_main_rolls.`items`
        WHERE
          LOWER(`art`)=LOWER("'.$art.'")
            AND
          `id`!="'.$item_id.'"
      ');
    }

    static function get_arts(string $art): array
    {
      return DB::select(/** @lang text */ '
        SELECT
          `id`,
          `name`
        FROM
          jaco_main_rolls.`items`
        WHERE
          LOWER(`art`)=LOWER("'.$art.'")
      ') ?? [];
    }

    static function delete_sklad_storage_items(int $item_id)
    {
      DB::delete(/** @lang text */ '
       DELETE FROM
         jaco_main_rolls.`sklad_storage_items`
       WHERE
         `item_id`=:item_id
      ', ['item_id' => $item_id]);
    }

    static function insert_sklad_storage_items(int $storage_id, int $item_id): void
    {
      DB::insert(/** @lang text */ '
        INSERT INTO jaco_main_rolls.`sklad_storage_items` (storage_id, item_id)
          VALUES(
            "'.$storage_id.'",
            "'.$item_id.'"
          )
      ');
    }

    static function update_items_is_main_0(string $art): int
    {
      return DB::update(/** @lang text */'
        UPDATE
          jaco_main_rolls.`items`
        SET
          `is_main`=0
        WHERE
          LOWER(`art`)=LOWER("'.$art.'")
      ');
    }

    static function update_items_is_main_1(int $main_item_id): int
    {
      return DB::update(/** @lang text */'
       UPDATE
          jaco_main_rolls.`items`
        SET
          `is_main`=1
        WHERE
          `id`="'.$main_item_id.'"
      ');
    }

    static function delete_items_allergens(int $item_id): void
    {
      DB::delete(/** @lang text */ '
       DELETE FROM
        jaco_main_rolls.`items_allergens`
       WHERE
        `item_id`=:item_id
      ', ['item_id' => $item_id]);
    }

    static function delete_items_allergens_other(int $item_id): void
    {
      DB::delete(/** @lang text */ '
       DELETE FROM
        jaco_main_rolls.`items_allergens_other`
       WHERE
        `item_id`=:item_id
      ', ['item_id' => $item_id]);
    }

    static function insert_items_allergens(int $allergen_id, int $item_id): void
    {
      DB::insert(/** @lang text */ '
        INSERT INTO jaco_main_rolls.`items_allergens` (all_id, item_id)
        VALUES
        (
          "'.$allergen_id.'",
          "'.$item_id.'"
        )
      ');
    }

    static function insert_items_allergens_other(int $allergen_id, int $item_id): void
    {
      DB::insert(/** @lang text */ '
        INSERT INTO jaco_main_rolls.`items_allergens_other` (all_id, item_id)
        VALUES
        (
          "'.$allergen_id.'",
          "'.$item_id.'"
        )
      ');
    }

    static function update_one_item(string $name, int $pf_id, int $vend_percent, int $ed_izmer_id, int $los_percent, string $pq, int $percent, string $art, int $is_show, int $cat_id, string $time_min, int $time, string $time_dop_min, int $time_dop, string $time_min_other, int $time_other, int $app_id, string $name_for_vendor, int $w_pf, int $w_trash, int $w_item, int $two_user, int $min_count, int $max_count_in_m, string $date, int $item_id): int
    {
      return DB::update(/** @lang text */'
       UPDATE
          jaco_main_rolls.`items`
        SET
          name="'.$name.'",
          pf_id="'.$pf_id.'",
          vend_percent="'.$vend_percent.'",
          ed_izmer_id="'.$ed_izmer_id.'",
          los_percent="'.$los_percent.'",
          pq="'.$pq.'",
          percent="'.$percent.'",
          art="'.$art.'",
          is_show="'.$is_show.'",

          cat_id="'.$cat_id.'",

          time_min="'.$time_min.'",
          time_sec="'.$time.'",

          time_dop_min="'.$time_dop_min.'",
          time_dop_sec="'.$time_dop.'",

          time_min_other="'.$time_min_other.'",
          time_sec_other="'.$time_other.'",

          app_id="'.$app_id.'",

          name_for_vendor="'.$name_for_vendor.'",

          w_pf="'.$w_pf.'",
          w_trash="'.$w_trash.'",
          w_item="'.$w_item.'",
          two_user="'.$two_user.'",

          min_count="'.$min_count.'",

          max_count_in_m="'.$max_count_in_m.'",
          date_update="'.$date.'"
        WHERE
          `id`="'.$item_id.'"
      ');
    }

    static function get_items_list(): array
    {
      return DB::select(/** @lang text */ '
        SELECT * FROM jaco_main_rolls.`items`
      ') ?? [];
    }

    static function insert_new_item(string $name, string $name_for_vendor, int $cat_id, int $pf_id, string $art, int $ed_izmer_id, int $los_percent, string $pq, int $percent, int $vend_percent, string $time_min, int $time, string $time_dop_min, int $time_dop, string $time_min_other, int $time_other, int $app_id, int $w_pf, int $w_trash, int $w_item, int $two_user, int $max_count_in_m, string $date): int
    {
      DB::insert(/** @lang text */ '
        INSERT INTO jaco_main_rolls.`items`
          (name, name_for_vendor, cat_id, pf_id, art, ed_izmer_id, los_percent, pq, percent, vend_percent, time_min, time_sec, time_dop_min, time_dop_sec, time_min_other, time_sec_other, app_id, w_pf, w_trash, w_item, two_user, is_show, max_count_in_m, date_update)
         VALUES(
          "'.$name.'",
          "'.$name_for_vendor.'",
          "'.$cat_id.'",
          "'.$pf_id.'",
          "'.$art.'",
          "'.$ed_izmer_id.'",
          "'.$los_percent.'",
          "'.$pq.'",
          "'.$percent.'",
          "'.$vend_percent.'",

          "'.$time_min.'",
          "'.$time.'",

          "'.$time_dop_min.'",
          "'.$time_dop.'",

          "'.$time_min_other.'",
          "'.$time_other.'",

          "'.$app_id.'",

          "'.$w_pf.'",
          "'.$w_trash.'",
          "'.$w_item.'",
          "'.$two_user.'",
          1,
          "'.$max_count_in_m.'",
          "'.$date.'"
        )
     ');

      return DB::getPdo()->lastInsertId();
    }

}
