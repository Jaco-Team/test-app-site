<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\DB;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Factory;

class Firebase
{

    //Метод для рассылки через push в приложении для клиентов
    public function send_user_push()
    {
        //https://firebase-php.readthedocs.io/en/7.15.0/cloud-messaging.html

        $firebase = (new Factory)->withServiceAccount(__DIR__.'/Firebase.json');

        $messaging = $firebase->createMessaging();

        /*
         * Логика такая: смотрим в БД, какие пуши надо рассылать $pushes,
         * формируем общий массив с токенами $deviceTokensRaw и массив с токенами для рассылки (пустой) $deviceTokens.
         * Затем делаем проверку для каждого пуша, высылали ли мы каждому юзеру этот пуш.
         * Если нет, то добавляем токен в массив $deviceTokens и отправляем пуш.
         * Пишем в push_appuser_send, что пуш юзеру был отправлен.
         * После отправки каждого пуша, очищаем массив $deviceTokens.
         */
        //Формируем общий массив с токенами
        $deviceTokensRaw = DB::select('
            SELECT
                UNT.`token`, UNT.`user_id`
            FROM `jaco_main_rolls`.`user_notif_token` UNT
                LEFT JOIN `jaco_main_rolls`.`site_users` SU
                ON SU.`id` = UNT.`user_id`
            WHERE SU.`is_active`= 1
                AND UNT.`user_id` is not null
                AND
            SU.`id` in (112565, 103162)
                AND
            UNT.`token` != "e4F3Gr3BSO6OpSZHPfKKEI:APA91bEcT-KmHtASJWIs7EAdeEsqBOzRMIgVC9GIDKwaaotn3iRU6ioz4BiA4xdnFQhdTvfb6xtZLZGy5Y3CJpluT7iK4SktBE8xYlaz5EBZ13psx49-XSwm-e-alXRZiM5GvoBsKGqY"
        ');
        $deviceTokens = [];

        //Список актуальных пушей
        $pushes = DB::select('
            SELECT
                *
            FROM `jaco_site_rolls`.`push`
            WHERE `is_active`=1
                AND `is_send`=1
                AND `is_auth`=1
        ');
        foreach ($pushes as $push) {
            foreach ($deviceTokensRaw as $deviceTokenRaw) {
                //Проверяем, высылали ли юзеру этот пуш
                $push_check = DB::select('
                    SELECT *
                    FROM `jaco_site_rolls`.`push_appuser_send` PAS
                    WHERE PAS.`push_id` = '.$push->id.'
                        AND PAS.`site_user_id` = '.$deviceTokenRaw->user_id.'
                        AND PAS.`app_token` = "'.$deviceTokenRaw->token.'"
                        AND PAS.`is_send`=1
                ');
                if (!$push_check) {
                    echo 'try to send push<br>';
                    $deviceTokens[] = $deviceTokenRaw->token;
                    $res = DB::insert('
                    INSERT INTO `jaco_site_rolls`.`push_appuser_send` (
                        `push_id`,
                        `site_user_id`,
                        `app_token`,
                        `is_send`
                    ) VALUES (
                        '.$push->id.',
                        '.$deviceTokenRaw->user_id.',
                        "'.$deviceTokenRaw->token.'",
                        1
                    )
                ');
                }
            }

            $message = CloudMessage::fromArray([
                //    'token' => $deviceToken,
                'notification' => [
                    'title' => $push->title,
                    'body' => $push->text
                ],
                'data' => [
                    'title' => $push->title,
                    'body' => $push->text
                ],
                'apns' => [
                    'headers' => [
                        'apns-priority' => '10',
                    ],
                    'payload' => [
                        'aps' => [
                            'sound' => 'default',
                        ]
                    ],
                ],
            ]);
            if (count($deviceTokens) != 0) {
                $report = $messaging->sendMulticast($message, $deviceTokens);
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

        //$res = $messaging->send($message);
        //dd($report);
    }

    //Метод для рассылки через push для клиентов, что заказ готов в кафе
    public function send_order_done_push(){
        //https://firebase-php.readthedocs.io/en/7.15.0/cloud-messaging.html

        $firebase = (new Factory)->withServiceAccount(__DIR__.'/Firebase.json');

        $messaging = $firebase->createMessaging();

        $points = DB::select('
            SELECT
                `id`,
                `base`
            FROM `jaco_main_rolls`.`points`
            WHERE
                `is_active`=1
        ');

        $start = microtime(true);

        while (true) {

            if ( (int)(microtime(true) - $start) > 58 ) {
                die();
            }

            foreach ($points as $point) {
                $app_orders = DB::select('
                    SELECT
                        o.`id`,
                        otn.`notif_token`
                    FROM
                        '.$point->base.'.`orders` o
                        LEFT JOIN '.$point->base.'.`order_types_notif` otn
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

                foreach ($app_orders as $key => $order) {

                    $message = CloudMessage::fromArray([
                        'token' => $order->notif_token,
                        'notification' => [
                            'title' => "Заказ готов!",
                            'body' => 'Заказ №'.$order->id.' готов и ожидает вас в кафе'
                        ],
                        'data' => [
                            'title' => "Заказ готов!",
                            'body' => 'Заказ №'.$order->id.' готов и ожидает вас в кафе'
                        ],
                        'apns' => [
                            'headers' => [
                                'apns-priority' => '10',
                            ],
                            'payload' => [
                                'aps' => [
                                    'sound' => 'default',
                                ]
                            ],
                        ],
                    ]);

                    $res = $messaging->send($message);

                    DB::update('
                        UPDATE
                            '.$point->base.'.`order_types_notif`
                        SET
                            `is_send`=?
                        WHERE
                            `order_id`=?
                        ',
                        ['1', $order->id],
                    );
                }
            }

            sleep(2);
        }
    }
}
