<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Model_site_user_manager extends Model
{
  use HasFactory;

  static function check(int $app_id): object|null
  {
    return DB::selectOne(/** @lang text */'
      SELECT
        `is_graph`,
        `kind`
      FROM
        jaco_main_rolls.`appointment`
      WHERE
        `id`=:app_id
    ', ['app_id' => $app_id]);
  }






}
