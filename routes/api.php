<?php

use Inertia\Inertia;
use App\Http\Controllers\C_UserController;
use App\Http\Controllers\C_IAController;
use App\Http\Controllers\C_BillController;
use App\Http\Controllers\C_MailController;
use App\Http\Controllers\C_NetwoorkController;
use App\Http\Controllers\C_StripeController;
use Illuminate\Support\Facades\Route;


Route::group(['prefix' => '/users'], function () {
  Route::name('api.')->controller(C_UserController::class)->group(function () {
    Route::get('/{id}', 'getUser')->name('getUser');
    //Route::get('/users/{id}', [C_UserController::class, 'getUser']);
    Route::post('/sertchUser', 'sertchgetUser')->name('sertchgetUser'); // a voir
    Route::post('/', 'addUser')->name('addUser');
    Route::put('/{id}', 'updateUser')->name('updateUser');
    Route::delete('/{id}', 'deleteUser')->name('deleteUser');
  });
});
Route::group(['prefix' => "/post"], function () {
  Route::name('post.')->controller(C_NetwoorkController::class)->group(function () {
    Route::post('/', 'createAndPublishPost')->name('creatAndPublish');
  });
});
Route::group(['prefix' => '/bill'], function () {
  Route::name('api.')->controller(C_BillController::class)->group(function () {
    Route::post('/generatebill', 'generateBill')->name('generateBill');
  });
});
Route::group(['prefix' => '/ia'], function () {
  Route::name('api.')->controller(C_IAController::class)->group(function () {
    // Route::post('/generateIA', 'generatprompt')->name('generatprompt');
    Route::post('/generateIA', [C_IAController::class, 'generatpromptgemini'])->name('generatpromptgemini');
  });
});
Route::group(['prefix' => '/stripe'], function () {
  Route::name('stripe.')->controller(C_IAController::class)->group(function () {
    // Route::post('/generateIA', 'generatprompt')->name('generatprompt');
    Route::post('/create-payment-intent', [C_StripeController::class, 'createPaymentIntent']);

  });
});

Route::group(['prefix' => '/auth'], function () {
  Route::name('auth.')->group(function () {
    Route::post('/register', [C_UserController::class, 'register'])->name('register');
    // Route::group(['prefix' => '/facebook'],function (){
    //     Route::name('facebook.')->controller(FacebookController::class)->group(function(){
    //         Route::get('/', 'index')->name('facebook');
    //         Route::get('/callback', 'callback')->name('callback');
    //     });
    // });

  });
});
Route::group(['prefix' => '/mail'], function () {
  route::name('api.')->controller(C_MailController::class)->group(function () {
    Route::post('/generateMail', 'generateMail')->name('generateMail');
    Route::post('/AddMail/{id}', 'AddMail')->name('AddMail');
    Route::get('/ListDestinataireClient/{id}', 'getListDestinataire')->name('getListDestinataire');
    Route::post('/AddDestinataireClient/{id}', 'AddListDestinataire')->name('AddListDestinataire');
    Route::put('/UpdateDestinataireClient/{id}', 'UpdateListDestinataire')->name('UpdateListDestinataire');
    Route::delete('/ListDestinataireClient/{id}', 'deleteListDestinataire')->name('deleteListDestinataire');
  });
});

// Route::group(['prefix' => '/mail'], function () {
//     Route::post('/send', [C_MailController::class, 'sendEmail'])->name('send');
//     Route::post('/attachment', 'attachment')->name('attachment');
// });




// require __DIR__.'/settings.php';
// require __DIR__.'/auth.php';
