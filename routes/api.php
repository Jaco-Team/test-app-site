<?php

use App\Http\Resources\GlobalResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\V1\IndexController;
use App\Http\Middleware\CheckToken;

use App\Http\Controllers\Controller_option_to_win;
use App\Http\Controllers\Controller_concenter;
use App\Http\Controllers\Controller_app_work;
use App\Http\Controllers\Controller_site_push;
use App\Http\Controllers\Controller_stat_time_orders;
use App\Http\Controllers\Controller_site_page_text;
use App\Http\Controllers\Controller_site_price_lavel;
use App\Http\Controllers\Controller_site_user_manager;

use App\Http\Controllers\Controller_vendor_mini;
use App\Http\Controllers\Controller_site_clients;
use App\Http\Controllers\Controller_cafe_edit;

use App\Http\Controllers\Controller_module_stat_order;

use App\Http\Controllers\Controller_sklad_items_module;

Route::get('/', [IndexController::class, 'index'])->name('home');
Route::get('/one', [IndexController::class, 'get_last'])->name('home');

Route::middleware([CheckToken::class])->group(function () {

    Route::prefix('option_to_win')->group(function () {
      Route::any('/get_all', [Controller_option_to_win::class, 'get_all']);
      Route::any('/get_one', [Controller_option_to_win::class, 'get_one']);
      Route::any('/get_all_for_new', [Controller_option_to_win::class, 'get_all_for_new']);
      Route::any('/change_active', [Controller_option_to_win::class, 'change_active']);
      Route::any('/save_new', [Controller_option_to_win::class, 'save_new']);
      Route::any('/save_edit', [Controller_option_to_win::class, 'save_edit']);
    });

    Route::prefix('concenter')->group(function () {
      Route::any('/get_all', [Controller_concenter::class, 'get_all']);
      Route::any('/get_orders', [Controller_concenter::class, 'get_orders']);
      Route::any('/get_order_new', [Controller_concenter::class, 'get_order_new']);
      Route::any('/fake_user', [Controller_concenter::class, 'fake_user']);
      Route::any('/close_order_center', [Controller_concenter::class, 'close_order_center']);
    });

    Route::prefix('app_work')->group(function () {
      Route::any('/get_all', [Controller_app_work::class, 'get_all']);
      Route::any('/get_one', [Controller_app_work::class, 'get_one']);
      Route::any('/get_all_for_new', [Controller_app_work::class, 'get_all_for_new']);
      Route::any('/save_check', [Controller_app_work::class, 'save_check']);
      Route::any('/save_new', [Controller_app_work::class, 'save_new']);
      Route::any('/save_edit', [Controller_app_work::class, 'save_edit']);
    });

    Route::prefix('site_push')->group(function () {
      Route::any('/get_all', [Controller_site_push::class, 'get_all']);
      Route::any('/get_all_for_new', [Controller_site_push::class, 'get_all_for_new']);
      Route::any('/get_one', [Controller_site_push::class, 'get_one']);
      Route::any('/save_active', [Controller_site_push::class, 'save_active']);
      Route::any('/save_new', [Controller_site_push::class, 'save_new']);
      Route::any('/save_edit', [Controller_site_push::class, 'save_edit']);
    });

    Route::prefix('stat_time_orders')->group(function () {
      Route::any('/get_all', [Controller_stat_time_orders::class, 'get_all']);
      Route::any('/get_stat', [Controller_stat_time_orders::class, 'get_stat']);
    });

    Route::prefix('site_page_text')->group(function () {
      Route::any('/get_all', [Controller_site_page_text::class, 'get_all']);
      Route::any('/get_all_for_new', [Controller_site_page_text::class, 'get_all_for_new']);
      Route::any('/get_one', [Controller_site_page_text::class, 'get_one']);
      Route::any('/save_new', [Controller_site_page_text::class, 'save_new']);
      Route::any('/save_edit', [Controller_site_page_text::class, 'save_edit']);
    });

    Route::prefix('site_price_lavel')->group(function () {
      Route::any('/get_all', [Controller_site_price_lavel::class, 'get_all']);
      Route::any('/get_all_for_new', [Controller_site_price_lavel::class, 'get_all_for_new']);
      Route::any('/save_new', [Controller_site_price_lavel::class, 'save_new']);
      Route::any('/get_one', [Controller_site_price_lavel::class, 'get_one']);
      Route::any('/save_one_price', [Controller_site_price_lavel::class, 'save_one_price']);
      Route::any('/save_edit', [Controller_site_price_lavel::class, 'save_edit']);
      Route::any('/export_file_xls', [Controller_site_price_lavel::class, 'export_file_xls']);
      Route::any('/import_file_xls', [Controller_site_price_lavel::class, 'import_file_xls']);
    });

    Route::prefix('site_user_manager')->group(function () {
      Route::any('/get_all', [Controller_site_user_manager::class, 'get_all']);
      Route::any('/getUsers', [Controller_site_user_manager::class, 'get_users']);
      Route::any('/getAllForNew', [Controller_site_user_manager::class, 'get_all_for_new']);
      Route::any('/getUser', [Controller_site_user_manager::class, 'get_one_user']);
      Route::any('/saveNewUser', [Controller_site_user_manager::class, 'save_new_user']);
      Route::any('/saveEditUser', [Controller_site_user_manager::class, 'save_edit_user']);
    });

    Route::prefix('vendor_mini')->group(function () {
      Route::any('/get_all', [Controller_vendor_mini::class, 'get_all']);
      Route::any('/get_vendor_info', [Controller_vendor_mini::class, 'get_vendor_info']);
    });

    Route::prefix('site_clients')->group(function () {
      Route::any('/get_all', [Controller_site_clients::class, 'get_all']);
      Route::any('/get_clients', [Controller_site_clients::class, 'get_clients']);
      Route::any('/get_orders', [Controller_site_clients::class, 'get_orders']);
      Route::any('/get_one_client', [Controller_site_clients::class, 'get_one_client']);
      Route::any('/save_edit_client', [Controller_site_clients::class, 'save_edit_client']);
      Route::any('/get_one_order', [Controller_site_clients::class, 'get_one_order']);
      Route::any('/save_comment', [Controller_site_clients::class, 'save_comment']);
      Route::any('/save_action', [Controller_site_clients::class, 'save_action']);
      Route::any('/save_promo', [Controller_site_clients::class, 'save_promo']);
      Route::any('/get_code', [Controller_site_clients::class, 'get_code']);
      Route::any('/export_file_xls', [Controller_site_clients::class, 'export_file_xls']);
    });

    Route::prefix('cafe_edit')->group(function () {
      Route::any('/get_all', [Controller_cafe_edit::class, 'get_all']);
      Route::any('/get_one', [Controller_cafe_edit::class, 'get_one']);
      Route::any('/save_edit_point_info', [Controller_cafe_edit::class, 'save_edit_point_info']);
      Route::any('/save_edit_point_sett', [Controller_cafe_edit::class, 'save_edit_point_sett']);
      Route::any('/save_edit_point_rate', [Controller_cafe_edit::class, 'save_edit_point_rate']);
      Route::any('/save_edit_point_pay', [Controller_cafe_edit::class, 'save_edit_point_pay']);
      Route::any('/stop_cafe', [Controller_cafe_edit::class, 'stop_cafe']);
      Route::any('/save_new_point', [Controller_cafe_edit::class, 'save_new_point']);
      Route::any('/stop_zone', [Controller_cafe_edit::class, 'stop_zone']);
    });

    Route::prefix('module_stat_order')->group(function () {
      Route::any('/get_all', [Controller_module_stat_order::class, 'get_all']);
      Route::any('/get_stat_days', [Controller_module_stat_order::class, 'get_stat_days']);
      Route::any('/get_stat_month', [Controller_module_stat_order::class, 'get_stat_month']);
    });

    Route::prefix('sklad_items_module')->group(function () {
      Route::any('/get_all', [Controller_sklad_items_module::class, 'get_all']);
      Route::any('/get_all_for_new', [Controller_sklad_items_module::class, 'get_all_for_new']);
      Route::any('/get_search', [Controller_sklad_items_module::class, 'get_search']);
      Route::any('/get_one', [Controller_sklad_items_module::class, 'get_one']);
      Route::any('/save_check', [Controller_sklad_items_module::class, 'save_check']);
      Route::any('/get_one_hist', [Controller_sklad_items_module::class, 'get_one_hist']);
      Route::any('/check_art', [Controller_sklad_items_module::class, 'check_art']);
      Route::any('/save_edit', [Controller_sklad_items_module::class, 'save_edit']);
      Route::any('/save_new', [Controller_sklad_items_module::class, 'save_new']);
    });

});
