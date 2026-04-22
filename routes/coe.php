<?php

use App\Http\Controllers\CoeRecordController;
use App\Http\Middleware\AuthMiddleware;
use Illuminate\Support\Facades\Route;


$app_name = env('APP_NAME', '');

Route::redirect('/', "/$app_name");

Route::prefix($app_name)->middleware(AuthMiddleware::class)->group(function () {

    Route::get('/coe-records', [CoeRecordController::class, 'index'])->name('coe-records.index');
});
