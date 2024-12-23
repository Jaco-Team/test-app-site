<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Model_site_clients extends Model
{
    use HasFactory;

    static function save_history(int $user_login, int $editor_id, string $date_time, string $event, mixed $value)
    {
      DB::insert(/** @lang text */ '
        INSERT INTO jaco_site_rolls.`from_site_user_events` (user_login, editor_id, date_time, event, value)
          VALUES (
            "'.$user_login.'",
            "'.$editor_id.'",
            "'.$date_time.'",
            "'.$event.'",
            "'.$value.'"
          )
      ');

      return DB::getPdo()->lastInsertId();
    }

    static function get_site_clients(string $check_login): array
    {
      return DB::select(/** @lang text */ '
        SELECT
          `id`,
          `name`,
          `login`
        FROM
          jaco_main_rolls.`site_users`
        WHERE
          '.$check_login.'
        ORDER BY
          `login`
      ') ?? [];
    }

    static function get_client_info(string $login): object
    {
      return DB::selectOne(/** @lang text */ '
        SELECT
          `id`,
          `name`,
          `date_reg`,
          `date_bir`,
          `all_count_order`,
          `count_dev`,
          `count_pic`,
          `summ_dev`,
          `summ_pic`,
          `summ`,
          `promo_name`,
          `mail`,
          `login`
        FROM
           jaco_main_rolls.`site_users` su
        WHERE
           `login` LIKE :login
      ', ['login' => $login]);
    }

    static function save_data_client(string $login, string|null $date_bir, string|null $mail): bool
    {
      return DB::update(/** @lang text */'
        UPDATE
          jaco_main_rolls.`site_users`
        SET
          `date_bir`=:date_bir,
          `mail`=:mail
        WHERE
          `login`=:login
      ', ['login' => $login, 'date_bir' => $date_bir, 'mail' => $mail]);
    }

    static function get_all_points(): array
    {
      return DB::select(/** @lang text */ '
        SELECT
          *
        FROM
          jaco_main_rolls.`points`
        WHERE
          `is_active`=1
      ') ?? [];
    }

    static function get_client_orders(string $base, int $id, string $number): array
    {
      //CONCAT(DATE(IF(o.`unix_date_time_preorder`=0, o.`date_time_order`, o.`date_time_preorder`)), " ", `close_date_time_order` ) as date_time,

      return DB::select(/** @lang text */ '
        SELECT
          if(o.`type_order`=1, "Доставка", "Самовывоз") as new_type_order,

          IF( o.`is_delete` = 0,
            IF(o.`unix_date_time_preorder`=0, IF( o.`status_order`=6, CONCAT(DATE(IF(o.`unix_date_time_preorder`=0, o.`date_time_order`, o.`date_time_preorder`)), " ", `close_date_time_order` ), o.`date_time_order` ), o.`date_time_preorder`),
            o.`date_time_delete`
          ) as date_time,


          `summ_promo`+`summ_div` as summ,
          o.`id` as order_id,
          p.`id` as point_id,
          p.`addr` as point,
          o.`is_delete`
        FROM
          '.$base.'.`orders` o
          LEFT JOIN jaco_main_rolls.`points` p
              ON
            p.`id`="'.$id.'"
        WHERE
          o.`number` LIKE "'.$number.'"
              AND
          o.`status_order`!=0
        ORDER BY
          o.`date_time_order` DESC
      ') ?? [];
    }

    static function get_city_name(int $point_id): object
    {
      return DB::selectOne(/** @lang text */ '
        SELECT
          c.`name`
        FROM
          jaco_main_rolls.`points` p
          LEFT JOIN jaco_main_rolls.`cities` c
            ON
            c.`id`=p.`city_id`
        WHERE
          p.`id`=:point_id
      ', ['point_id' => $point_id]);
    }

    static function get_err_order(int $point_id, int $order_id): object|null
    {
      return DB::selectOne(/** @lang text */ '
        SELECT
          e.`date_time_desc`,
          e.`order_desc`,
          e.`text_win`,
          e.`order_id`
        FROM
          jaco_site_rolls.`err_orders` e
        WHERE
          e.`order_id`=:order_id AND e.`point_id`=:point_id
      ', ['point_id' => $point_id, 'order_id' => $order_id]);
    }

    static function get_items_order(string $base, int $order_id): array
    {
      return DB::select(/** @lang text */ '
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
          oi.`order_id`="'.$order_id.'"
      ') ?? [];
    }

    static function get_order(string $base, int $order_id, int $point_id, string $date_time, string $date, string $city_name): object|null
    {
      return DB::selectOne(/** @lang text */ '
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
                      TIME_TO_SEC("'.$date_time.'"),
                      TIME_TO_SEC( FROM_UNIXTIME( o.`unix_start_stol` ) )
                  )- TIME_TO_SEC(o.`date_time_order`) ),


              if( o.`type_order` = 1,
                  SEC_TO_TIME( TIME_TO_SEC( CONCAT("'.$date.'", " ", o.`close_date_time_order`) ) - TIME_TO_SEC(o.`date_time_order`) ),
                  SEC_TO_TIME( TIME_TO_SEC( o.`give_data_time` ) - TIME_TO_SEC(o.`date_time_order`) )
              )
          ), 0)
        ) as textTime,

        ROUND(if( o.`status_order` !=6 AND o.`is_delete` = 0,
            SEC_TO_TIME(
                if( o.`unix_date_time_preorder`=0,
                    TIME_TO_SEC("'.$date_time.'"),
                    TIME_TO_SEC( FROM_UNIXTIME( o.`unix_start_stol` ) )
                )- TIME_TO_SEC(o.`date_time_order`) ),

            if( o.`type_order` = 1,
                SEC_TO_TIME( TIME_TO_SEC( CONCAT("'.$date.'", " ", o.`close_date_time_order`) ) - TIME_TO_SEC(o.`date_time_order`) ),
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
          o.`id`="'.$order_id.'"
      ');
    }

    static function get_check_pos(string $base, int $order_id): object|null
    {
      return DB::selectOne(/** @lang text */ '
        SELECT
          `dist`
        FROM
          '.$base.'.`order_driver_cash_other_position`
        WHERE
          `order_id`="'.$order_id.'"
      ');
    }

    static function get_check_pos_drive(string $base, int $order_id): object|null
    {
      return DB::selectOne(/** @lang text */ '
        SELECT
          *
        FROM
          '.$base.'.`order_driver_cash_other`
        WHERE
          `order_id`="'.$order_id.'"
      ');
    }

    static function get_items_order_2(string $base, int $order_id): array
    {
      return DB::select(/** @lang text */ '
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
          oif.`item_id`
      ') ?? [];
    }

    static function get_client_err_orders(string $login): array
    {
      return DB::select(/** @lang text */ '
        SELECT
          er.`date_time_desc`,
          er.`order_desc`,
          er.`text_win`,
          er.`order_id`,
          p.`addr` as point
        FROM
          jaco_site_rolls.`err_orders` er
        LEFT JOIN jaco_main_rolls.`points` p
            ON
              p.`id`=er.`point_id`
        WHERE
          (er.`phone_order` LIKE "'.$login.'"
            OR
          er.`phone_dop` LIKE "'.$login.'")
        ORDER BY
          er.`date_time_desc` DESC
      ') ?? [];
    }

    static function get_client_comments(string $login): array
    {
      return DB::select(/** @lang text */ '
       SELECT
          uc.`id`,
          `date_add`,
          `comment`,
          u.`short_name` as name,
          u1.`short_name` as name_close,
          uca.`date_time`,
          uca.`description`,
          uca.`raiting`,
          uca.`sale`
       FROM
          jaco_site_rolls.`user_comments` uc
          LEFT JOIN jaco_main_rolls.`users` u
            ON
              u.`id`=uc.`user_id_add`
          LEFT JOIN jaco_site_rolls.`user_comments_actions` uca
            ON
              uca.`comment_id`=uc.`id`
          LEFT JOIN jaco_main_rolls.`users` u1
            ON
              u1.`id`=uca.`user_id`
       WHERE
          `user_number` LIKE :login
       ORDER BY
          `date_add` DESC
      ', ['login' => $login]) ?? [];
    }

    static function insert_new_comment(string $user_number, string $date_add, int $user_id_add, string $comment): int
    {
      DB::insert(/** @lang text */ '
        INSERT INTO jaco_site_rolls.`user_comments` (user_number, date_add, user_id_add, comment)
          VALUES (
            "'.$user_number.'",
            "'.$date_add.'",
            "'.$user_id_add.'",
            "'.$comment.'"
          )
      ');

      return DB::getPdo()->lastInsertId();
    }

    static function insert_new_action(int $comment_id, int $user_id, string $date_time, string $description, int $raiting, int $sale): int
    {
      DB::insert(/** @lang text */ '
        INSERT INTO jaco_site_rolls.`user_comments_actions` (comment_id, user_id, date_time, description, raiting, sale)
        VALUES (
          "'.$comment_id.'",
          "'.$user_id.'",
          "'.$date_time.'",
          "'.$description.'",
          "'.$raiting.'",
          "'.$sale.'"
        )
      ');

      return DB::getPdo()->lastInsertId();
    }

    static function get_promo_name(int $promo_id): object|null
    {
      return DB::selectOne(/** @lang text */ '
        SELECT
          `name`
        FROM
          jaco_site_rolls.`promo`
        WHERE
          `id`=:promo_id
      ', ['promo_id' => $promo_id]) ?? [];
    }

    static function get_client(string $login): object|null
    {
      return DB::selectOne(/** @lang text */ '
        SELECT
          *
        FROM
          jaco_main_rolls.`site_users`
        WHERE
          `login`=:login
      ', ['login' => $login]);
    }

    static function insert_user_send_sms_lk(string $date_time, string $phone, string $text): void
    {
      DB::insert(/** @lang text */ '
        INSERT INTO
          jaco_main_rolls.`user_send_sms_lk` (date_time, phone, text, type)
        VALUES (
          "'.$date_time.'",
          "'.$phone.'",
          "'.$text.'",
          "1"
        )
      ');
    }

    static function insert_site_users_promo(int $user_id, int $promo_id): void
    {
      DB::insert(/** @lang text */ '
        INSERT INTO
          jaco_main_rolls.`site_users_promo`
          (user_id, promo_id)
        SELECT
          "'.$user_id.'" as user_id,
          `id` as promo_id
        FROM
          jaco_site_rolls.`promo`
        WHERE `id`="'.$promo_id.'"
      ');
    }

    static function get_client_send_sms(int $user_id): array
    {
      return DB::select(/** @lang text */ '
        SELECT
          `date_time`,
          `code`
        FROM
          jaco_main_rolls.`site_users_send_sms`
        WHERE
          `user_id` = :user_id
        ORDER BY
          `date_time` DESC
      ', ['user_id' => $user_id]) ?? [];
    }

    static function get_client_login_yandex(int $login): array
    {
      return DB::select(/** @lang text */ '
        SELECT
          `date_time`
        FROM
          jaco_main_rolls.`site_users_login_yandex`
        WHERE
          `login` LIKE :login
        ORDER BY
          `date_time` DESC
      ', ['login' => $login]) ?? [];
    }

    static function insert_user_sms_code(int $user_id, string $code, string $date_time): string|false
    {
      DB::insert(/** @lang text */ '
        INSERT INTO
          jaco_main_rolls.`site_users_send_sms` (user_id, code, date_time)
        VALUES (
          "'.$user_id.'",
          "'.$code.'",
          "'.$date_time.'"
        )
      ');

      return DB::getPdo()->lastInsertId();
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

    static function get_points(string $city_id): array
    {
      return DB::select(/** @lang text */ '
          SELECT
            *
          FROM
            jaco_main_rolls.`points`
          WHERE
            `is_active`=1
            '.$city_id.'
        ') ?? [];
    }

    static function get_orders(int $point_id, string $addr, string $base, string $date_start, string $date_end, string $search_data, string $item_id): array
    {
      return DB::select( '
        SELECT
            "'.$point_id.'" as point_id,
            "'.$addr.'" as point_addr,
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
            ((o.`date_time_order` BETWEEN "'.$date_start.' 00:00:00" AND "'.$date_end.' 23:59:59" AND o.`unix_date_time_preorder`=0)
                OR
            o.`date_time_preorder` BETWEEN "'.$date_start.' 00:00:00" AND "'.$date_end.' 23:59:59")
                AND
            o.`status_order`!=0
            '.$search_data.'
                AND
            o.`id` IN (SELECT oi.`order_id` FROM '.$base.'.`order_items` oi '.$item_id.')
        ORDER BY
                unix_time DESC
      ') ?? [];
    }

}
