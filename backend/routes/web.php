<?php

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

Route::get('/test-mail', function () {

    Mail::raw('Laravel gửi mail thành công!', function ($message) {
        $message->to('phamngocha785@gmail.com')
                ->subject('Test Mail');
    });

    return 'Da gui mail';
});