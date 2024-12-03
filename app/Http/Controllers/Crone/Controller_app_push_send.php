<?php

namespace App\Http\Controllers\Crone;

use App\Http\Controllers\Api\Firebase;
use App\Http\Controllers\Controller;
use App\Models\Crone\Model_app_push_send;

//Общий контроллер для рассылки пушей в приложении
class Controller_app_push_send extends Controller
{

  //Метод для рассылки через push в приложении для клиентов
  public function send_user_push(): void
  {
    /*
     * Логика такая: смотрим в БД, какие пуши надо рассылать $pushes,
     * формируем общий массив с токенами $deviceTokensRaw и массив с токенами для рассылки (пустой) $deviceTokens.
     * Затем делаем проверку для каждого пуша, высылали ли мы каждому юзеру этот пуш.
     * Если нет, то добавляем токен в массив $deviceTokens и отправляем пуш.
     * Пишем в push_appuser_send, что пуш юзеру был отправлен.
     * После отправки каждого пуша, очищаем массив $deviceTokens.
     */
    //Формируем общий массив с токенами
    $deviceTokensRaw = Model_app_push_send::get_device_tokens();
    $deviceTokens = [];

    //Список актуальных пушей
    $pushes = Model_app_push_send::get_push_active_send_auth();
    foreach ($pushes as $push) {
      foreach ($deviceTokensRaw as $deviceTokenRaw) {
        //Проверяем, высылали ли юзеру этот пуш
        $push_check = Model_app_push_send::get_push_appuser_send($push->id, $deviceTokenRaw->user_id, $deviceTokenRaw->token);
        if (!$push_check) {
          echo 'try to send push<br>';
          $deviceTokens[] = $deviceTokenRaw->token;
          $res = Model_app_push_send::insert_push_appuser_send($push->id, $deviceTokenRaw->user_id, $deviceTokenRaw->token);
        }
      }

      if (count($deviceTokens) != 0) {
        $report = Firebase::send_multicast_push($deviceTokens, $push->title, $push->text);
        echo "successes: ";
        echo $report->successes()->count();
        echo " / failures: ";
        echo $report->failures()->count();
        echo "\n";
        $deviceTokens = [];
      } else {
        echo 'no tokens for push id: '.$push->id. "\n";
      }
    }
  }

  //Метод для рассылки через push для клиентов, что заказ готов в кафе
  public function send_order_done_push(): void
  {

    $points = Model_app_push_send::get_active_points();

    $start = microtime(true);

    while (true) {

      if ( (int)(microtime(true) - $start) > 58 ) {
        die();
      }

      foreach ($points as $point) {
        $app_orders = Model_app_push_send::get_token_from_order($point->base);

        foreach ($app_orders as $key => $order) {

          $report = Firebase::send_one_push($order->notif_token, 'Заказ готов!', 'Заказ №'.$order->id.' готов и ожидает вас в кафе');

          Model_app_push_send::update_order_push_status($order->id, $point->base);

        }
      }

      sleep(2);
    }
  }
}
