<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

//use DefStudio\Telegraph\Models\TelegraphBot;

use \App\Http\Controllers\Controller_sms;

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
