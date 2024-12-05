<?php

namespace App\Models\Crone;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Model_app_push_send extends Model
{

    //Список активных рассылок, которые нужно отправить
    static function get_push_active_send_auth(): array
    {
      return DB::select('
            SELECT
                *
            FROM `jaco_site_rolls`.`push`
            WHERE `is_active`=1
                AND `is_send`=1
                AND `is_auth`=1
        ') ?? [];
    }

    //Список токенов для рассылки тест!
    static function get_device_tokens(): array
    {
      return DB::select('
            SELECT
                UNT.`token`, UNT.`user_id`
            FROM `jaco_main_rolls`.`user_notif_token` UNT
                LEFT JOIN `jaco_main_rolls`.`site_users` SU
                ON SU.`id` = UNT.`user_id`
            WHERE SU.`is_active`= 1
                AND UNT.`user_id` is not null
        ') ?? [];
    }

    static function get_push_appuser_send($push_id, $site_user_id, $app_token): array
    {
      return DB::select('
                    SELECT *
                    FROM `jaco_site_rolls`.`push_appuser_send` PAS
                    WHERE PAS.`push_id` = '.$push_id.'
                        AND PAS.`site_user_id` = '.$site_user_id.'
                        AND PAS.`app_token` = "'.$app_token.'"
                        AND PAS.`is_send`=1
                ');
    }

    static function insert_push_appuser_send($push_id, $site_user_id, $app_token)
    {
      return DB::insert('
                    INSERT INTO `jaco_site_rolls`.`push_appuser_send` (
                        `push_id`,
                        `site_user_id`,
                        `app_token`,
                        `is_send`
                    ) VALUES (
                        '.$push_id.',
                        '.$site_user_id.',
                        "'.$app_token.'",
                        1
                    )
                ');
    }

    static function get_active_points(): array
    {
      return DB::select('
                  SELECT
                    `id`,
                    `base`
                  FROM `jaco_main_rolls`.`points`
                  WHERE
                    `is_active`=1
        ');
    }

    static function get_token_from_order($point_base): array
    {
      return DB::select('
        SELECT
          o.`id`,
          otn.`notif_token`
        FROM
          '.$point_base.'.`orders` o
           LEFT JOIN '.$point_base.'.`order_types_notif` otn
              ON
              otn.`order_id` = o.`id`
        WHERE
          o.`type_order`=2
            AND
          o.`status_order`=4
						AND
					o.`is_delete`=0
            AND
          otn.`is_send`=0
            AND
          otn.`notif_token`!=""
      ') ?? [];
    }

    static function update_order_push_status($order_id, $point_base): void
    {
      DB::update('
                        UPDATE
                            '.$point_base.'.`order_types_notif`
                        SET
                            `is_send`=?
                        WHERE
                            `order_id`=?
                        ',
        ['1', $order_id],
      );
    }
}
