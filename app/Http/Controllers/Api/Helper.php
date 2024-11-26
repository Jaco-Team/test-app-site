<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\DB;
use PhpParser\Node\Scalar\String_;

class Helper
{
    static function getInfoModule($module)
    {
        return DB::selectOne(/** @lang text */ '
            SELECT
                sm.`name`
            FROM
                jaco_main_rolls.`appointment_template` a_t
                LEFT JOIN jaco_main_rolls.`sklad_modules` sm
                    ON
                        sm.`id`=a_t.`modul_id`
            WHERE
                sm.`key_query`= :module
            LIMIT 1',
            ['module' => $module]
        ) ?? [ 'name' => '' ];
    }

    static function getInfoByMy($login)
    {
        return DB::selectOne(/** @lang text */'
            SELECT
                u.`name`,
                u.`short_name`,
                u.`id`,
                up.`city_id`,
                up.`point_id`,
                u.`inn`,
                up.`appointment_id` as app_id,
                app.`type` as app_type,
                app.`kind`,
                up.`appointment_id` as app_id
            FROM
                jaco_main_rolls.`users` u
                LEFT JOIN jaco_main_rolls.`user_privileges` up
                    ON
                        up.`user_id`=u.`id`
                LEFT JOIN jaco_main_rolls.`appointment` app
                    ON
                        app.`id`=up.`appointment_id`
            WHERE
                u.`login`= :login',
            ['login' => $login]
        );
    }

    static function getMyPointList($city_id, $point_id): array
    {
        if((int)$city_id == -1) {
            return DB::select(/** @lang text */'
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
                    p.`id`!=0
                ORDER BY
                    p.`city_id`'
            ) ?? [];
        }else{
            return DB::select(/** @lang */ '
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
                    p.`city_id`="'.$city_id.'"
                      AND
                    ("'.$point_id.'"=-1
                      OR
                    p.`id`="'.$point_id.'")
                      AND
                    p.`id`!=0
                ORDER BY
                    p.`city_id`'
            ) ?? [];
        }

    }

    static function checkAccesModule($user_id, $module)
    {
        return DB::selectOne(/** @lang text */'
            SELECT
                a_t.`value`
            FROM
                jaco_main_rolls.`user_privileges` up
                LEFT JOIN jaco_main_rolls.`appointment_template` a_t
                    ON
                        a_t.`appointment_id`=up.`appointment_id`
                LEFT JOIN jaco_main_rolls.`sklad_modules` sm
                    ON
                        sm.`id`=a_t.`modul_id`
            WHERE
                up.`user_id`= :user_id
                    AND
                sm.`key_query`= :module',
            ['user_id' => $user_id, 'module' => $module]
        );
    }

    static function getDopAccessModule($app_id, $module): array
    {
        return DB::select(/** @lang text */'
            SELECT
                ag.`param`,
                atg.`value`,
                ag.`category`
            FROM
                jaco_main_rolls.`appointment_group` ag
                LEFT JOIN jaco_main_rolls.`appointment_template_group` atg
                    ON
                        atg.`group_id`=ag.`id`
                LEFT JOIN jaco_main_rolls.`sklad_modules` sm
                    ON
                        sm.`id`=ag.`module_id`
            WHERE
                atg.`appointment_id`= :app_id
                    AND
                sm.`key_query`= :module',
            ['app_id' => $app_id, 'module' => $module]
        ) ?? [];
    }

    static function searchAccessModule($id, $category, $array): string|bool
    {
        foreach ($array as $val) {
            if ($val['param'] === $id && $val['category'] === $category) {
                return $val['value'];
            }
        }
        return false;
    }

    static function get_base($point_id)
    {
        return DB::selectOne(/** @lang text */'
            SELECT
                `base`
            FROM
                jaco_main_rolls.`points`
            WHERE
                `id`=:point_id',
            ['point_id' => $point_id]
        )->base ?? '';
    }

    static function parseToken($token)
    {
        $login = Helper::base64url_decode( $token );
        $login = explode('-_-', $login);

        return [
            'login' => $login[0],
            'id' => $login[1]
        ];
    }

    static function base64url_encode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    static function base64url_decode($data)
    {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }
}
