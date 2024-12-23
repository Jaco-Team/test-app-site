<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Model_vendor_mini extends Model
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

    static function get_vendors(int $city_id): array
    {
        return DB::select('
           SELECT
					v.`id`,
					v.`name`,
					v.`is_show`,
					v.`is_priority`
				FROM
					jaco_main_rolls.`vendors` v
                LEFT JOIN jaco_main_rolls.`vendor_cities` vc
						ON
							vc.`vendor_id`=v.`id`
				WHERE
					vc.`city_id`="'.$city_id.'"
						OR
					"'.$city_id.'"=-1
						OR
					"'.$city_id.'"=0
				GROUP BY
					v.`id`
				ORDER BY
					v.`is_show` DESC,
					v.`name`
        ') ?? [];
    }

    static function get_vendor(int $vendor_id):  object|null
    {
        return DB::selectOne('
          SELECT
                *
            FROM
                jaco_main_rolls.`vendors`
            WHERE
                `id`= '.$vendor_id.'
        ') ?? [];
    }

    static function get_mails(int $vendor_id): array
    {

        $all_points = DB::select('
            SELECT
                `id`,
                `addr` as name,
                `city_id`
            FROM
               jaco_main_rolls.`points`
            WHERE
                `id`!=0
            ORDER BY
                `city_id`
        ') ?? [];
      /* */

        $mails = DB::select('
          SELECT
                `id`,
					      `point_id`,
					      `mail`,
					      `comment`
            FROM
                jaco_main_rolls.`vendor_point_mail`
            WHERE
                `vendor_id`= '.$vendor_id.'
        ') ?? [];

        foreach( $mails as $key => $val ){
            foreach( $all_points as $point ){ // $point    object(stdClass)
                if( (int)$point->id == (int)$val->point_id ){
                    $mails[ $key ]['point_id'] = $point;
                }
            }
        }
       return $mails;
    }

    static function get_items(int $vendor_id): array
    {
       return DB::select('
          SELECT
              i.`name`
            FROM
                jaco_main_rolls.`vendor_items` vi
              LEFT JOIN jaco_main_rolls.`items` i
                ON
                  vi.`item_id`=i.`id`
            WHERE
              i.`is_show`=1
                AND
              vi.`vendor_id`="'.(int)$vendor_id.'"
            ORDER BY
              i.`name`
			') ?? [];
    }
}
