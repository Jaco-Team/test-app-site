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

});
