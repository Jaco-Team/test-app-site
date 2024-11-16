<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Model_promo extends Model
{
    static function checkActivePromo(string $name, int $city_id, string $date_end){
        return DB::selectOne(/** @lang text */ '
            SELECT
                *
            FROM
                jaco_site_rolls.`promo`
            WHERE
                (`city_id`= :city_id
                    OR
                `city_id`=0)
                    AND
                `name` LIKE :name
                    AND
                (`date2` < :date_end
                    OR
                `is_delete`=1)
        ', ['city_id' => $city_id, 'name' => $name, 'date_end' => $date_end]);
    }

    static function getCityList(int $city_id): array
    {
        return DB::select(/** @lang text */ '
            SELECT
                `id`
            FROM
                jaco_main_rolls.`cities`
            WHERE
                (`id`= :city_id
                    OR
                :city_id =0)
                    AND
                `is_show`=1
        ', ['city_id' => $city_id]);
    }

    static function insertEvent(int $city_id, string $date, int $promo_id, int $user_id)
    {
        DB::insert(/** @lang text */ '
            INSERT INTO jaco_site_rolls.`events` (point_id, date, type, value, user_id)
            VALUES(
                :city_id,
                :date,
                "3",
                :promo_id,
                :user_id
            )
        ', ['city_id' => $city_id, 'date' => $date, 'promo_id' => $promo_id, 'user_id' => $user_id]);
    }

    static function savePromo(array $promo)
    {
        //скидка
        if((int)$promo['promo_action'] == 1) {
            DB::insert(/** @lang text */ "
                INSERT INTO jaco_site_rolls.`promo`(
                    creator_id,
                    date_create,
                    name,
                    def_count,
                    count,
                    type_order,
                    promo_action,
                    promo_type_sale,
                    count_promo,
                    promo_type,

                    promo_items_1,
                    promo_cat_1,

                    time1,
                    time2,
                    date1,
                    date2,
                    d1,
                    d2,
                    d3,
                    d4,
                    d5,
                    d6,
                    d7,

                    city_id,
                    point_id,

                    promo_conditions,
                    promo_conditions_items,
                    promo_summ,
                    promo_summ_to,
                    promo_conditions_cat,
                    promo_type_cat,
                    promo_con_summ,
                    promo_count_cart,
                    promo_con_count,

                    coment,
                    condition_text,
                    only_site,
                    free_drive,
                    show_kit,
                    only_first_order
                ) VALUES (
                    '".$promo['user_id']."',
					'".$promo['date_create']."',
					'".$promo['promo_name']."',
					'".$promo['promo_in_count']."',
					'".$promo['promo_in_count']."',
					'".$promo['promo_type_order']."',
					'".$promo['promo_action']."',
					'".$promo['promo_type_sale']."',
					'".$promo['count_promo']."',
					'".$promo['promo_type']."',



					'".$promo['promo_items']."',
					'".$promo['promo_cat']."',

					'".$promo['time_start']."',
					'".$promo['time_end']."',
					'".$promo['date_start']."',
					'".$promo['date_end']."',
					'".$promo['day_1']."',
					'".$promo['day_2']."',
					'".$promo['day_3']."',
					'".$promo['day_4']."',
					'".$promo['day_5']."',
					'".$promo['day_6']."',
					'".$promo['day_7']."',

					'".$promo['city_id']."',
					'".$promo['promo_point']."',

					'".$promo['promo_conditions']."',
					'".$promo['promo_conditions_items']."',
					'".$promo['promo_summ']."',
					'".($promo['promo_summ_to'] ?? 0)."',
					'0',
					'0',
					'0',
					'0',
					'0',

					'".$promo['about_promo_text']."',
					'".$promo['condition_promo_text']."',

					'".$promo['site_only']."',
					'".$promo['free_drive']."',
					'".($promo['show_kit'] ?? 0)."',
					'".($promo['site_first_order'] ?? 0)."'
                )
            ");

            return DB::getPdo()->lastInsertId();
        }

        //добавляет товары
        if((int)$promo['promo_action'] == 2) {
            DB::insert(/** @lang text */ "
                INSERT INTO jaco_site_rolls.`promo`(
                    creator_id,
                    date_create,
                    name,
                    def_count,
                    count,
                    type_order,
                    promo_action,
                    promo__items,

                    time1,
                    time2,
                    date1,
                    date2,
                    d1,
                    d2,
                    d3,
                    d4,
                    d5,
                    d6,
                    d7,

                    city_id,
                    point_id,

                    promo_conditions,
                    promo_conditions_items,
                    promo_summ,
                    promo_summ_to,
                    promo_conditions_cat,
                    promo_type_cat,
                    promo_con_summ,
                    promo_count_cart,
                    promo_con_count,

                    coment,
                    condition_text,
                    only_site,
                    free_drive,
                    show_kit,
                    only_first_order
                ) VALUES (
                    '".$promo['user_id']."',
					'".$promo['date_create']."',
					'".$promo['promo_name']."',
					'".$promo['promo_in_count']."',
					'".$promo['promo_in_count']."',
					'".$promo['promo_type_order']."',
					'".$promo['promo_action']."',
					'".$promo['promo_items_add']."',

					'".$promo['time_start']."',
					'".$promo['time_end']."',
					'".$promo['date_start']."',
					'".$promo['date_end']."',
					'".$promo['day_1']."',
					'".$promo['day_2']."',
					'".$promo['day_3']."',
					'".$promo['day_4']."',
					'".$promo['day_5']."',
					'".$promo['day_6']."',
					'".$promo['day_7']."',

					'".$promo['city_id']."',
					'".$promo['promo_point']."',

					'".$promo['promo_conditions']."',
					'".$promo['promo_conditions_items']."',
					'".$promo['promo_summ']."',
					'".($promo['promo_summ_to'] ?? 0)."',
					'0',
					'0',
					'0',
					'0',
					'0',

					'".$promo['about_promo_text']."',
					'".$promo['condition_promo_text']."',

					'".$promo['site_only']."',
					'".$promo['free_drive']."',
					'".($promo['show_kit'] ?? 0)."',
					'".($promo['site_first_order'] ?? 0)."'
                )
            ");

            return DB::getPdo()->lastInsertId();
        }

        //товар за цену
        if((int)$promo['promo_action'] == 3) {
            DB::insert(/** @lang text */ "
                INSERT INTO jaco_site_rolls.`promo`(
                    creator_id,
                    date_create,
                    name,
                    def_count,
                    count,
                    type_order,
                    promo_action,

                    add_items_on_price,
                    promo_type_sale,

                    time1,
                    time2,
                    date1,
                    date2,
                    d1,
                    d2,
                    d3,
                    d4,
                    d5,
                    d6,
                    d7,

                    city_id,
                    point_id,

                    promo_conditions,
                    promo_conditions_items,
                    promo_summ,
                    promo_summ_to,
                    promo_conditions_cat,
                    promo_type_cat,
                    promo_con_summ,
                    promo_count_cart,
                    promo_con_count,

                    coment,
                    condition_text,
                    only_site,
                    free_drive,
                    show_kit,
                    only_first_order
                ) VALUES (
                    '".$promo['user_id']."',
					'".$promo['date_create']."',
					'".$promo['promo_name']."',
					'".$promo['promo_in_count']."',
					'".$promo['promo_in_count']."',
					'".$promo['promo_type_order']."',
					'".$promo['promo_action']."',

					'".$promo['promo_items_sale']."',
					'-1',

					'".$promo['time_start']."',
					'".$promo['time_end']."',
					'".$promo['date_start']."',
					'".$promo['date_end']."',
					'".$promo['day_1']."',
					'".$promo['day_2']."',
					'".$promo['day_3']."',
					'".$promo['day_4']."',
					'".$promo['day_5']."',
					'".$promo['day_6']."',
					'".$promo['day_7']."',

					'".$promo['city_id']."',
					'".$promo['promo_point']."',

					'".$promo['promo_conditions']."',
					'".$promo['promo_conditions_items']."',
					'".$promo['promo_summ']."',
					'".($promo['promo_summ_to'] ?? 0)."',
					'0',
					'0',
					'0',
					'0',
					'0',

					'".$promo['about_promo_text']."',
					'".$promo['condition_promo_text']."',

					'".$promo['site_only']."',
					'".$promo['free_drive']."',
					'".($promo['show_kit'] ?? 0)."',
					'".($promo['site_first_order'] ?? 0)."'
                )
            ");

            return DB::getPdo()->lastInsertId();
        }

    }
}
