<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\DB;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Factory;

class Firebase
{

    //отправка массовых пушей в приложении
    static function send_multicast_push($deviceTokens, $title, $body) {
      //https://firebase-php.readthedocs.io/en/7.15.0/cloud-messaging.html

      $firebase = (new Factory)->withServiceAccount(__DIR__.'/Firebase.json');

      $messaging = $firebase->createMessaging();

      $message = CloudMessage::fromArray([
        'notification' => [
          'title' => $title,
          'body' => $body
        ],
        'data' => [
          'title' => $title,
          'body' => $body
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

      $report = $messaging->sendMulticast($message, $deviceTokens);

      return $report;
    }

    //отправка единичного пуша в приложении
    static function send_one_push($deviceToken, $title, $body) {
      //https://firebase-php.readthedocs.io/en/7.15.0/cloud-messaging.html

      $firebase = (new Factory)->withServiceAccount(__DIR__.'/Firebase.json');

      $messaging = $firebase->createMessaging();

      $message = CloudMessage::fromArray([
        'token' => $deviceToken,
        'notification' => [
          'title' => $title,
          'body' => $body
        ],
        'data' => [
          'title' => $title,
          'body' => $body
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

      $report = $messaging->send($message);

      return $report;
    }
}
