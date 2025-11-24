<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return ['message' => 'Feature Voting API', 'version' => '1.0.0'];
});
