<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Model_concenter extends Model
{
    use HasFactory;

    static function get_all_cities(): array
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

    static function get_all_points(): array
    {
        return DB::select(/** @lang text */ '
            SELECT
                `id`,
                `addr` as name,
                `city_id`
            FROM
                jaco_main_rolls.`points`
            WHERE
                `is_active`=1
        ') ?? [];
    }

    static function get_orders_new(int $point_id, string $base, string $date): array
    {
        return DB::select( '
            SELECT
                "'.$point_id.'" as point_id,
                o.`id`,

                o.`client_id`,

                (SELECT su.`id` FROM jaco_main_rolls.`site_users` su WHERE su.`login`=o.`number` LIMIT 1) as user_id,

                TIME_FORMAT(o.`date_time_order`, "%H:%i:%S") as date_time_order,
                TIME_FORMAT(o.`date_time_preorder`, "%H:%i:%S") as date_time_preorder,
                TIME_FORMAT(o.`date_time_preorder`, "%H:%i:%S") as date_time_preorder_text,
                TIME_FORMAT(o.`give_data_time`, "%H:%i:%S") as give_data_time,
                TIME_FORMAT(o.`date_time_delete`, "%H:%i:%S") as date_time_delete,
                IF(o.`close_date_time_order` IS NOT NULL, o.`close_date_time_order`, "") as close_order,

                UNIX_TIMESTAMP(if( o.`unix_date_time_preorder`=0, o.`date_time_order`, o.`date_time_preorder` )) as unix_time,

                IF(o.`unix_date_time_preorder`=0, 0, 1) as is_preorder,

                o.`street`,
                o.`home`,

                IF(o.`type_order`=1,
                    IF(o.`free_drive`=1, IF(o.`summ_promo`!=0, o.`summ_promo`, o.`summ_promo`+1), o.`summ_promo`+o.`summ_div`),
                    o.`summ_promo`) as order_price,
                o.`is_delete`,

                IF(o.`type_order`=1, "Доставка",
                    IF(o.`type_order`=2, "Самовывоз",
                        IF(o.`type_order`=3, "Зал",
                            IF(o.`type_order`=4, "Зал с собой", "Ошибка")))) as type_order,

                o.`type_order` as type_origin,

                IF(o.`type_pay`=1, "Нал", "Безнал") as type_pay,

                IF(o.`status_order`=1, "В очереди",
                    IF(o.`status_order`=2, "Готовится",
                        IF(o.`status_order`=3, "Готов на кухне",
                        IF(o.`status_order`=4, "Собран",
                            IF(o.`status_order`=5, "В пути",
                                IF(o.`status_order`=6, "У клиента", "Ошибочка")))))) as status,
                o.`status_order`,
                IF(o.`number`=0, "", o.`number`) as number,

                o.`unix_time_to_client`,
                o.`unix_start_stol`,
                o.`close_unix_date_time_order`,
                o.`date_time_order` as date_time_origin,
                o.`close_date_time_order`,
                o.`give_data_time` as give_data_time_origin,

                if(o.`unix_start_stol` != 0, SUBSTRING_INDEX(FROM_UNIXTIME(o.`unix_start_stol`), " ", -1), "") as time_start_stol,
                ( SELECT IF( odi.`id` IS NULL, 5, odi.`plus_time_pred` ) FROM '.$base.'.`orders_dop_info` odi WHERE o.`id`=odi.`order_id` LIMIT 1 ) as plus_time
            FROM
                '.$base.'.`orders` o
            WHERE
                ((o.`date_time_order` BETWEEN "'.$date.' 00:00:00" AND "'.$date.' 23:59:59" AND o.`unix_date_time_preorder`=0)
                    OR
                o.`date_time_preorder` BETWEEN "'.$date.' 00:00:00" AND "'.$date.' 23:59:59")
                    AND
                o.`status_order`!=0
            ORDER BY
                    unix_time DESC
        ') ?? [];
    }

    static function get_city_by_point(int $point_id): object
    {
        return DB::selectOne(/** @lang text */'
            SELECT
                c.`name`,
                c.`id`
            FROM
                jaco_main_rolls.`points` p
                LEFT JOIN jaco_main_rolls.`cities` c
                    ON
                        c.`id`=p.`city_id`
            WHERE
                p.`id`=:point_id
        ', ['point_id' => $point_id]);
    }

    static function get_order_items(int $order_id, string $base): array
    {
        return DB::select(/** @lang text */'
            SELECT
                oi.`id`,
                i.`name`,
                i.`id` as item_id,
                oi.`count`,
                oi.`price`
            FROM
                '.$base.'.`order_items` oi
                LEFT JOIN jaco_site_rolls.`items` i
                    ON
                        i.`id`=oi.`item_id`
            WHERE
                oi.`order_id`=:order_id
        ', ['order_id' => $order_id]);
    }

    static function get_order_items_ready(int $order_id, string $base): array
    {
        return DB::select(/** @lang text */'
            SELECT
                i.`name`,
                COUNT(*) as count,
                if( SUM( IF(oif.`ready`=1, 1, 0) ) = COUNT(*), 1, 0 ) as ready,
                SUM( IF(oif.`ready`=1, 1, 0) ) as isready,
                SUM( IF(oif.`ready`!=1, 1, 0) ) as noready
            FROM
                '.$base.'.`order_items_full_log` oif
                LEFT JOIN jaco_site_rolls.`items` i
                    ON
                        i.`id`=oif.`item_id`
            WHERE
                oif.`order_id`="'.$order_id.'"
            GROUP BY
                i.`name`
        ') ?? [];
    }

    static function get_order_info(int $point_id, int $order_id, string $city_name, string $base): object
    {
        return DB::selectOne(/** @lang text */'
            SELECT
                o.`id` as order_id,
                o.`number`,
                IF(o.`free_drive` = 1, IF(o.`summ_promo` = 0, 1, o.`summ_promo`), o.`summ_promo`+o.`summ_div`) as sum_order,
                IF(o.`free_drive` = 1, IF(o.`summ_promo` = 0, 1, 0), o.`summ_div`) as sum_div,
                o.`type_order` as type_order_,
                o.`status_order`,
                "'.$point_id.'" as point_id,
                o.`is_delete`,
                o.`date_time_delete`,

                o.`sdacha`,

                CONCAT(
                    if( o.`type_order` = 1,
                        if( o.`status_order` != 6,
                            "В пути:",
                            "Доставили за:"
                        ),
                        if( o.`status_order` < 4,
                            "Готовят:",
                            "Приготовили за:"
                        )
                    ),

                    " ",

                    ROUND(if( o.`status_order` !=6 AND o.`is_delete` = 0,
                        SEC_TO_TIME(
                            if( o.`unix_date_time_preorder`=0,
                                TIME_TO_SEC("'.date('Y-m-d H:i:s').'"),
                                TIME_TO_SEC( FROM_UNIXTIME( o.`unix_start_stol` ) )
                            )- TIME_TO_SEC(o.`date_time_order`) ),


                        if( o.`type_order` = 1,
                            SEC_TO_TIME( TIME_TO_SEC( CONCAT("'.date('Y-m-d').'", " ", o.`close_date_time_order`) ) - TIME_TO_SEC(o.`date_time_order`) ),
                            SEC_TO_TIME( TIME_TO_SEC( o.`give_data_time` ) - TIME_TO_SEC(o.`date_time_order`) )
                        )
                    ), 0)
                ) as textTime,


                ROUND(if( o.`status_order` !=6 AND o.`is_delete` = 0,
                    SEC_TO_TIME(
                        if( o.`unix_date_time_preorder`=0,
                            TIME_TO_SEC("'.date('Y-m-d H:i:s').'"),
                            TIME_TO_SEC( FROM_UNIXTIME( o.`unix_start_stol` ) )
                        )- TIME_TO_SEC(o.`date_time_order`) ),

                    if( o.`type_order` = 1,
                        SEC_TO_TIME( TIME_TO_SEC( CONCAT("'.date('Y-m-d').'", " ", o.`close_date_time_order`) ) - TIME_TO_SEC(o.`date_time_order`) ),
                        SEC_TO_TIME( TIME_TO_SEC( o.`give_data_time` ) - TIME_TO_SEC(o.`date_time_order`) )
                    )
                ), 0) as time,


                o.`street`,
                o.`home`,
                o.`pd`,
                o.`kv`,
                o.`et`,
                o.`fake_dom`,

                o.`comment`,

                IF(o.`type_order`=1, "Доставка",
                    IF(o.`type_order`=2, "Самовывоз",
                        IF(o.`type_order`=3, "Зал",
                            IF(o.`type_order`=4, "Зал с собой", "")))) as type_order,

                IF(o.`type_order` = 1, CONCAT(
                        "<b>Адрес доставки</b>: г. ",
                        "'.$city_name.' ",
                        o.`street`, " ",
                        o.`home`,
                        IF( LENGTH(o.`pd`) = 0 OR o.`pd` = 0, "", CONCAT(", Пд.: ", o.`pd`)),
                        IF( LENGTH(o.`et`) = 0 OR o.`et` = 0, "", CONCAT(", Эт.: ", o.`et`)),
                        IF( LENGTH(o.`kv`) = 0 OR o.`kv` = 0, "", CONCAT(", Кв.: ", o.`kv`)),
                        ""
                    ),
                    CONCAT("<b>Самовывоз</b>: г. ", "'.$city_name.' ", (SELECT p.`addr` FROM jaco_main_rolls.`points` p WHERE p.`id`="'.$point_id.'"))) as type_order_addr,

                IF(o.`type_order` = 1, CONCAT(
                    "г. ", "'.$city_name.', ",
                    o.`street`, " ",
                    o.`home`,
                    IF( LENGTH(o.`pd`) = 0 OR o.`pd` = 0, "", CONCAT(", Пд.: ", o.`pd`)),
                    IF( LENGTH(o.`et`) = 0 OR o.`et` = 0, "", CONCAT(", Эт.: ", o.`et`)),
                    IF( LENGTH(o.`kv`) = 0 OR o.`kv` = 0, "", CONCAT(", Кв.: ", o.`kv`))
                    ),
                    CONCAT("г. ", "'.$city_name.', ", (SELECT p.`addr` FROM jaco_main_rolls.`points` p WHERE p.`id`="'.$point_id.'"))) as type_order_addr_new,

                IF(o.`unix_date_time_preorder` != 0, o.`date_time_preorder`, o.`date_time_order`) as time_order,
                IF(o.`unix_date_time_preorder` != 0, "Предзаказ на", "Время заказа") as time_order_name,
                IF(o.`unix_date_time_preorder` = 0, 0, 1) as is_preorder,
                p.`name` as promo_name,
                p.`coment` as promo_text,
                o.`unix_time_to_client`,
                IF( u.`short_name`="", u.`name`, u.`short_name` ) as del_name,
                o.`del_type`,
                if(o.`delete_reason` is null, "", o.`delete_reason`) as delete_reason,

                o.`date_time_preorder` as date_time_preorder_or
            FROM
                '.$base.'.`orders` o
                LEFT JOIN jaco_site_rolls.`promo` p
                    ON
                        p.`id`=o.`promo_id`
                LEFT JOIN jaco_main_rolls.`users` u
                    ON
                        u.`id`=o.`del_id`
            WHERE
                o.`id`=:order_id
        ', ['order_id' => $order_id]);
    }

    static function get_order_info_min(string $base, int $order_id): object
    {
        return DB::selectOne(/** @lang text */'
            SELECT
                `status_order`,
                `type_order`,
                `is_delete`,
                `promo_id`,
                `online_pay`,
                `driver_id`,
                `summ_div_driver`,
                `unix_time_to_client`,
                `number`
            FROM
                '.$base.'.`orders`
            WHERE
                `id`=:order_id
        ', ['order_id' => $order_id]);
    }

    static function get_user_login_info(int $user_id): object|null
    {
        return DB::selectOne(/** @lang text */'
            SELECT
                `login`,
                `pwd`
            FROM
                jaco_main_rolls.`users`
            WHERE
                `id`= :user_id
        ', ['user_id' => $user_id]);
    }


    static function get_driver_close_dist_other(string $base, int $order_id, int $driver_id = 0)
    {
        return DB::selectOne(/** @lang text */'
            SELECT
                `dist`
            FROM
                '.$base.'.`order_driver_cash_other_position`
            WHERE
                `order_id`="'.$driver_id.'"
                    AND
                (`driver_id`="'.$driver_id.'"
                    OR
                "'.$driver_id.'" = 0)
        ')->dist ?? -1;
    }

    static function get_driver_close_dist(string $base, int $order_id)
    {
        return DB::selectOne(/** @lang text */'
            SELECT
                *
            FROM
                '.$base.'.`order_driver_cash_other`
            WHERE
                `order_id`=:order_id
        ', ['order_id' => $order_id]);
    }

    static function plus_time_pred(string $base, int $order_id): void
    {
        DB::update(/** @lang text */'
            UPDATE
                '.$base.'.`orders_dop_info`
            SET
                `plus_time_pred`=`plus_time_pred`+30
            WHERE
                `order_id`= :order_id
        ', ['order_id' => $order_id]);
    }

    static function update_time_to_client(string $base, int $order_id, string $time): void
    {
        DB::update(/** @lang text */'
            UPDATE
                '.$base.'.`orders`
            SET
                `unix_time_to_client`= :time
            WHERE
                `id`= :order_id
        ', ['order_id' => $order_id, 'time' => $time]);
    }

    static function return_active_promo(int $promo_id): void
    {
        DB::update(/** @lang text */'
             UPDATE
                jaco_site_rolls.`promo`
             SET
                `count`=`count`+1
             WHERE
                `id`="'.$promo_id.'"
        ');
    }

    static function update_del_order(string $base, int $order_id, int $user_id, string $text): bool
    {
        return DB::update(/** @lang text */'
             UPDATE
                '.$base.'.`orders`
             SET
                `is_delete`=1,
                `del_type`=1,
                `del_id`="'.$user_id.'",
                `date_time_delete`="'.date('Y-m-d H:i:s').'",
                `unix_date_time_delete`="'.time().'",
                `delete_reason`="'.$text.'"
             WHERE
                `id`="'.$order_id.'"
        ');
    }

    static function insert_order_driver_cash_other(string $base, int $driver_id, int $user_id, int $order_id, int $summ_div_driver, string $comment): int
    {
        DB::insert('
            INSERT INTO '.$base.'.`order_driver_cash_other` (driver, date_time, user_id, order_id, price, comment)
            VALUES (
                "'.$driver_id.'",
                "'.date('Y-m-d H:i:s').'",
                "'.$user_id.'",
                "'.$order_id.'",
                "'.$summ_div_driver.'",
                "'.$comment.'"
            )
        ');

        return DB::getPdo()->lastInsertId();
    }
}
