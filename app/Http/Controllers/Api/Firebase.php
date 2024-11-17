<?php

namespace App\Http\Controllers\Api;

use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Factory;

class Firebase
{
    public function send()
    {
        //https://firebase-php.readthedocs.io/en/7.15.0/cloud-messaging.html
        $deviceToken = 'dT49-1Isz0LMrKhpYMIudo:APA91bHo4l4BtndszR0ZTNxZOwSfng4i1ilqDVm6GS0h0iH1jZgKhiWustyqFSW99d7sj41-bGauJPxACuhVV3RKF8FaBUJftean10WC2LSczRgLp8iunEiETpLX81ydJbwQSUKybp4y';

        $deviceTokens = [$deviceToken, $deviceToken];

        $firebase = (new Factory)->withServiceAccount(__DIR__.'/Firebase.json');

        $messaging = $firebase->createMessaging();

        $message = CloudMessage::fromArray([
            'token' => $deviceToken,
            'notification' => [
                'title' => 'Hello from Firebase!',
                'body' => 'This is a test notification.'
            ],
            'data' => [
                'title' => 'Hello from Firebase!',
                'body' => 'This is a test notification.'
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

        //$res = $messaging->send($message);
        $report = $messaging->sendMulticast($message, $deviceTokens);

        echo $report->successes()->count();
        echo "<br />";
        echo $report->failures()->count();

        //dd($report);
    }
}
