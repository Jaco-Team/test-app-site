<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

//use Kreait\Laravel\Firebase\Facades\Firebase;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
//use DefStudio\Telegraph\Models\TelegraphBot;

use \App\Http\Controllers\Controller_sms;
use \App\Http\Controllers\Api\Firebase;
use App\Http\Controllers\Crone\Controller_app_push_send;

//use Kreait\Laravel\Firebase\Facades\Firebase;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Artisan::command('tester', function () {
    /** @var \DefStudio\Telegraph\Models\TelegraphBot $telegraphBot */

    $telegraphBot = \DefStudio\Telegraph\Models\TelegraphBot::find(1);

    /*dd($telegraphBot->registerCommands([
        '/hello' => 'command 1 description',
        '/world' => 'command 2 description',
        '/action' => 'command 3 description'
    ])->send());*/

    dd($telegraphBot->info());
});

Artisan::command('test_send_sms', function () {
    $sms = new Controller_sms();

    $sms->send_sms('89879340391', 'Hello world!');
});

//рассылка пушей в приложении для клиентов
Artisan::command('send_user_push', function () {
    $send_user_push = new Controller_app_push_send();
    $send_user_push->send_user_push();
});

//рассылка пушей для клиентов, что заказ готов в кафе
Artisan::command('send_order_done_push', function () {
    $firebase = new Controller_app_push_send();
    $firebase->send_order_done_push();
});

//Планировщик (крон) для рассылки пушей в приложении для клиентов
Schedule::command('send_user_push')
    ->everyThirtyMinutes()
    ->timezone('Europe/Samara')
    ->between('08:00', '21:00');
