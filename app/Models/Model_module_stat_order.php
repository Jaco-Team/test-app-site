<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Model_module_stat_order extends Model
{
    use HasFactory;

    static function get_stat_days(string $search_data, string $base, string $date): object|null
    {
      return DB::selectOne(/** @lang text */ '
        SELECT
					'.$search_data.'
				FROM
					'.$base.'.`orders` o
				WHERE
					((o.`date_time_order` BETWEEN "'.$date.' 00:00:00" AND "'.$date.' 23:59:59" AND o.`unix_date_time_preorder`=0)
              OR
          o.`date_time_preorder` BETWEEN "'.$date.' 00:00:00" AND "'.$date.' 23:59:59")
						AND
					o.`status_order`=6
						AND
					o.`is_delete`=0
      ');
    }

    static function get_stat_month(string $search_data, string $base, string $date_start, string $date_end): object|null
    {
      return DB::selectOne(/** @lang text */ '
        SELECT
          '.$search_data.'
        FROM
          '.$base.'.`orders` o
        WHERE
          ((o.`date_time_order` BETWEEN "'.$date_start.' 00:00:00" AND "'.$date_end.' 23:59:59" AND o.`unix_date_time_preorder`=0)
              OR
          o.`date_time_preorder` BETWEEN "'.$date_start.' 00:00:00" AND "'.$date_end.' 23:59:59")
            AND
          o.`status_order`=6
            AND
          o.`is_delete`=0
      ');
    }

    static function get_point_adv_days(int $point_id, string $date): array
    {
      return DB::select(/** @lang text */ '
        SELECT
          ac.`name`
        FROM
          jaco_site_rolls.`advertising_company` ac
					LEFT JOIN jaco_site_rolls.`advertising_company_points` acp
            ON
           acp.advertising_id = ac.id
        WHERE
          "'.$date.'" BETWEEN ac.`date_start` AND ac.`date_end`
            AND
          acp.point_id = "'.$point_id.'"
      ') ?? [];
    }

    static function get_point_adv_month(int $point_id, string $date_start, string $date_end): array
    {
      return DB::select(/** @lang text */ '
        SELECT
          ac.`name`
        FROM
          jaco_site_rolls.`advertising_company` ac
                    LEFT JOIN jaco_site_rolls.`advertising_company_points` acp
            ON
           acp.advertising_id = ac.id
        WHERE
          ((ac.`date_start` BETWEEN "'.$date_start.'" AND "'.$date_end.'")
            OR
          ac.`date_end` BETWEEN "'.$date_start.'" AND "'.$date_end.'")
            AND
          acp.point_id = "'.$point_id.'"
       ') ?? [];
    }

}
