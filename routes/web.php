<?php

use Inertia\Inertia;
use App\Http\Controllers\C_UserController;
use App\Http\Controllers\C_IAController;
use App\Http\Controllers\C_BillController;
use App\Http\Controllers\C_MailController;
use App\Http\Controllers\FacebookController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => '/auth'], function () {
  Route::name('auth.')->group(function () {
    Route::group(['prefix' => '/facebook'], function () {
      Route::name('facebook.')->controller(FacebookController::class)->group(function () {
        Route::get('/', 'index')->name('facebook');
        Route::get('/callback', 'callback')->name('callback');
      });
    });
  });
});


// Route::group(['prefix' => '/mail'], function () {
//     Route::post('/send', [C_MailController::class, 'sendEmail'])->name('send');
//     Route::post('/attachment', 'attachment')->name('attachment');
// });


Route::middleware(['auth', 'verified'])->group(function () {
  Route::get('dashboard', function () {
    return Inertia::render('dashboard');
  })->name('dashboard');
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
