<?php

namespace App\Http\Controllers;

use App\Models\Model_option_to_win;
use Illuminate\Http\Request;
use App\Http\Resources\GlobalResource;
use Illuminate\Support\Facades\Log;

class Controller_option_to_win extends Controller
{

    private static mixed $stages = array(
        array('id' => 1, 'name' => '1 Этап'),
        array('id' => 2, 'name' => '2 Этап'),
        array('id' => 3, 'name' => '3 Этап'),
        array('id' => 4, 'name' => 'Доставка'),
        array('id' => 5, 'name' => 'Поставщик'),
        array('id' => 6, 'name' => 'Котакт-центр'),
        array('id' => 7, 'name' => 'Кухня'),
        array('id' => 8, 'name' => 'Распределительный центр'),
        array('id' => 9, 'name' => 'Кассир'),
        array('id' => 10, 'name' => 'Офис'),
        array('id' => 11, 'name' => 'Ответственный за смену'),
        array('id' => 12, 'name' => 'Кухонный работник'),
    );

    public function get_all(Request $request): GlobalResource
    {
        return new GlobalResource([
            'module_info' => $request->module_info,
            'items' => Model_option_to_win::get_err_desc_items()
        ]);
    }

    public function get_one(Request $request): GlobalResource
    {
        $this_stages_1 = Model_option_to_win::get_this_err_stages('err_desc_stages_1', $request->data['id']);
        $this_stages_2 = Model_option_to_win::get_this_err_stages('err_desc_stages_2', $request->data['id']);
        $this_stages_3 = Model_option_to_win::get_this_err_stages('err_desc_stages_3', $request->data['id']);

        foreach( $this_stages_1 as $key => $this_stages ){
            foreach( Controller_option_to_win::$stages as $stage ){
                if( (int)$stage['id'] == (int)$this_stages->id ){
                    $this_stages_1[$key]->name = $stage['name'];
                }
            }
        }

        foreach( $this_stages_2 as $key => $this_stages ){
            foreach( Controller_option_to_win::$stages as $stage ){
                if( (int)$stage['id'] == (int)$this_stages->id ){
                    $this_stages_2[$key]->name = $stage['name'];
                }
            }
        }

        foreach( $this_stages_3 as $key => $this_stages ){
            foreach( Controller_option_to_win::$stages as $stage ){
                if( (int)$stage['id'] == (int)$this_stages->id ){
                    $this_stages_3[$key]->name = $stage['name'];
                }
            }
        }

        return new GlobalResource([
            'all_stages' => Controller_option_to_win::$stages,
            'this_stages_1' => $this_stages_1,
            'this_stages_2' => $this_stages_2,
            'this_stages_3' => $this_stages_3,
            'all_wins' => Model_option_to_win::get_this_win($request->data['id']),
            'this_wins' => Model_option_to_win::get_all_wins(),
            'err' => Model_option_to_win::get_this_err($request->data['id'])
        ]);
    }

    public function get_all_for_new(): GlobalResource
    {
        return new GlobalResource([
            'stages' => Controller_option_to_win::$stages,
            'err_to_win' => Model_option_to_win::get_all_wins()
        ]);
    }

    public function change_active(Request $request): GlobalResource
    {
        Model_option_to_win::set_active($request->data['is_active'], $request->data['id']);

        return new GlobalResource([
            'st' => true,
            'text' => ''
        ]);
    }

    public function save_new(Request $request): GlobalResource
    {
        $check = Model_option_to_win::get_err_desc_by_name($request->data['name']);

        if( $check ){
            return new GlobalResource([
                'st' => false,
                'text' => 'Такое название уже есть'
            ]);
        }

        $id = Model_option_to_win::insert_err_desc($request->data['name'], $request->data['need_photo']);

        if( (int)$id > 0 ){

            foreach($request->data['stage_err_1'] as $stage){
                Model_option_to_win::insert_stage_err_desc('err_desc_stages_1', $id, $stage['id']);
            }

            foreach($request->data['stage_err_2'] as $stage){
                Model_option_to_win::insert_stage_err_desc('err_desc_stages_2', $id, $stage['id']);
            }

            foreach($request->data['stage_err_3'] as $stage){
                Model_option_to_win::insert_stage_err_desc('err_desc_stages_3', $id, $stage['id']);
            }

            foreach($request->data['id_win'] as $win){
                Model_option_to_win::insert_err_desc_wins($id, $win['id']);
            }

            return new GlobalResource([
                'st' => true,
                'text' => ''
            ]);
        }else{
            return new GlobalResource([
                'st' => false,
                'text' => 'Ошибка записи'
            ]);
        }
    }

    public function save_edit(Request $request): GlobalResource
    {
        Model_option_to_win::update_err_desc($request->data['name'], $request->data['need_photo'], $request->data['is_active'], $request->data['id']);

        Model_option_to_win::del_stage_err_desc('err_desc_stages_1', $request->data['id']);
        Model_option_to_win::del_stage_err_desc('err_desc_stages_2', $request->data['id']);
        Model_option_to_win::del_stage_err_desc('err_desc_stages_3', $request->data['id']);
        Model_option_to_win::delete_err_desc_wins($request->data['id']);

        foreach($request->data['stage_err_1'] as $stage){
            Model_option_to_win::insert_stage_err_desc('err_desc_stages_1', $request->data['id'], $stage['id']);
        }

        foreach($request->data['stage_err_2'] as $stage){
            Model_option_to_win::insert_stage_err_desc('err_desc_stages_2', $request->data['id'], $stage['id']);
        }

        foreach($request->data['stage_err_3'] as $stage){
            Model_option_to_win::insert_stage_err_desc('err_desc_stages_3', $request->data['id'], $stage['id']);
        }

        foreach($request->data['id_win'] as $win){
            Model_option_to_win::insert_err_desc_wins($request->data['id'], $win['id']);
        }

        return new GlobalResource([
            'st' => true,
            'text' => ''
        ]);
    }
}
