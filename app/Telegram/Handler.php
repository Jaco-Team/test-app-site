<?php

//api - 40387D793BB3A8774AE85C5D915BE1C7
//secret - 4F92AED3B5778C469CE635556D698BED

namespace App\Telegram;

//use DefStudio\Telegraph\Facades\Telegraph;

use DefStudio\Telegraph\Facades\Telegraph;
use DefStudio\Telegraph\Models\TelegraphChat;

use Illuminate\Support\Facades\Log;
use DefStudio\Telegraph\Handlers\WebhookHandler;

use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;

use DefStudio\Telegraph\Keyboard\ReplyButton;
use DefStudio\Telegraph\Keyboard\ReplyKeyboard;

use App\Http\Controllers\Kandinsky;


class Handler extends WebhookHandler {

    public $imgSize = 512;

    public function hello($text){



        $this->reply('Hello World! '.$text);
    }

    public function world($text)
    {
        $this->reply("I can't understand your command: $text");
    }

    public function action1(): void
    {
        Telegraph::message('123')->send();
    }

    public function action(): void
    {
        $res = Telegraph::message('Выбери какое-то действие')
            ->keyboard(
                Keyboard::make()->buttons([
                    Button::make('Модели')->action('get_models'),
                    Button::make('Размер изображения')->action('sizes')
                ])
            )->send();

    }

    public function get_models(){
        $kd = Kandinsky::getInstance();

        $models = $kd::get_models();

        Telegraph::message( json_encode($models) )->send();
    }

    public function sizes(){
        Telegraph::message('Выбери размер изображения')
            ->keyboard(
                Keyboard::make()->buttons([
                    Button::make('128x128')->action('save_size')->param('size', '128'),
                    Button::make('512x512')->action('save_size')->param('size', '512'),
                    Button::make('1024x1024')->action('save_size')->param('size', '1024'),
                ])
            )->send();
    }

    public function save_size(){

        $this->imgSize = $this->data->get('size');
    }

    public function like()
    {

        /*$keyboard = ReplyKeyboard::make()
            ->row([
                ReplyButton::make('Send Contact')->requestContact(),
                ReplyButton::make('Send Location')->requestLocation(),
            ])
            ->row([
                ReplyButton::make('Quiz')->requestQuiz(),
            ]);

        Telegraph::message('Выбери какое-то действие 11')
            ->keyboard(
                $keyboard
            )->send();*/
        Telegraph::setTitle("my chat")->send();
        /*Telegraph::message('hello world')
            ->replyKeyboard(ReplyKeyboard::make()->buttons([
                ReplyButton::make('foo')->requestPoll(),
                ReplyButton::make('bar')->requestQuiz(),
                ReplyButton::make('baz')->webApp('https://webapp.dev'),
            ]))->send();*/
    }

    public function calculator_start(){
        //Telegraph::setTitle("my chat")->send();

        Telegraph::photo( public_path('/storage/2024_07_18_17_45_05.jpg') )->send();
    }

    public function generate_photo($text){
        //Telegraph::setTitle("my chat")->send();

        //$this->reply($this->imgSize);

        if($kd = Kandinsky::getInstance()){
            $pathToPhotoFile = $kd::promt($text, $this->imgSize);

            Telegraph::photo( public_path($pathToPhotoFile) )->send();

            //return $pathToPhotoFile;
        }


    }

    protected function handleChatMessage($text): void
    {

        Log::info( json_encode( $this->message->toArray(), JSON_UNESCAPED_UNICODE ) );

        $this->reply('test: '.$text);

    }

    protected function onFailure($throwable): void
    {
        if ($throwable instanceof NotFoundHttpException) {
            throw $throwable;
        }

        report($throwable);

        $this->reply('sorry man, I failed'. json_encode( $throwable ));
    }
}
