<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Model_option_to_win extends Model
{
    use HasFactory;

    static function get_err_desc_items()
    {
        return DB::select(/** @lang text */ '
            SELECT
                `id`,
                `name`,
                `is_active`,
                `sort`
            FROM
                jaco_site_rolls.`err_desc_items`
            ORDER BY
                `is_active` DESC
        ') ?? [];
    }

    static function get_this_err(int $id)
    {
        return DB::selectOne(/** @lang text */ '
            SELECT
                `id`,
                `name`,
                `is_active`,
                `need_photo`
            FROM
                jaco_site_rolls.`err_desc_items`
            WHERE
                `id`=:id
        ', ['id' => $id]);
    }

    static function get_this_err_stages(string $table, int $id)
    {
        return DB::select(/** @lang text */ '
            SELECT
                `stage_id` as id
            FROM
                jaco_site_rolls.`'.$table.'`
            WHERE
                `err_id`=:id
        ', ['id' => $id]) ?? [];
    }

    static function get_this_win(int $id)
    {
        return DB::select(/** @lang text */ '
            SELECT
                edw.`id`,
                edw.`name`
            FROM
                jaco_site_rolls.`err_desc_win` edw
                LEFT JOIN jaco_site_rolls.`err_desc_wins` edws
                    ON
                        edws.`win_id`=edw.`id`
            WHERE
                edws.`err_id`=:id
        ', ['id' => $id]) ?? [];
    }

    static function get_err_desc_by_name(string $name)
    {
        return DB::select(/** @lang text */ '
            SELECT
                `id`
            FROM
                jaco_site_rolls.`err_desc_items`
            WHERE
                LOWER(`name`)=LOWER(:name)
        ', ['name' => $name]);
    }

    static function get_all_wins()
    {
        return DB::select(/** @lang text */ '
            SELECT
                `id`,
                `name`
            FROM
                jaco_site_rolls.`err_desc_win`
        ') ?? [];
    }

    static function set_active(int $active, int $id)
    {
        DB::update(/** @lang text */ '
            UPDATE
                jaco_site_rolls.`err_desc_items`
            SET
                `is_active`=:active
            WHERE
                `id`=:id
        ', ['active' => $active, 'id' => $id]);
    }

    static function update_err_desc(string $name, int $need_photo, int $active, int $id)
    {
        DB::update(/** @lang text */ '
            UPDATE
                jaco_site_rolls.`err_desc_items`
            SET
                `name`=:name,
                `is_active`=:active,
                `need_photo`=:need_photo
            WHERE
                `id`=:id
        ', ['name' => $name, 'active' => $active, 'need_photo' => $need_photo, 'id' => $id]);
    }

    static function insert_err_desc(string $name, int $need_photo)
    {
        DB::insert(/** @lang text */ '
            INSERT INTO jaco_site_rolls.`err_desc_items` (name, need_photo)
                VALUES(
                    :name,
                    :need_photo
                )
        ', ['name' => $name, 'need_photo' => $need_photo]);

        return DB::getPdo()->lastInsertId();
    }

    static function insert_err_desc_wins(int $err_id, int $win_id)
    {
        DB::insert(/** @lang text */ '
            INSERT INTO jaco_site_rolls.`err_desc_wins` (err_id, win_id)
            VALUES(
                :err_id,
                :win_id
            )
        ', ['err_id' => $err_id, 'win_id' => $win_id]);
    }

    static function delete_err_desc_wins(int $err_id)
    {
        DB::insert(/** @lang text */ '
           DELETE FROM
                jaco_site_rolls.`err_desc_wins`
            WHERE
                `err_id`=:err_id
        ', ['err_id' => $err_id]);
    }

    static function del_stage_err_desc(string $table, int $id)
    {
        DB::delete(/** @lang text */ '
            DELETE FROM
                jaco_site_rolls.`'.$table.'`
            WHERE
                `err_id`=:id
        ', ['id' => $id]);
    }

    static function insert_stage_err_desc(string $table, int $id, int $stage)
    {
        DB::insert(/** @lang text */ '
            INSERT INTO jaco_site_rolls.`'.$table.'` (err_id, stage_id)
            VALUES(
                :id,
                :stage
            )
        ', ['id' => $id, 'stage' => $stage]);
    }
}
