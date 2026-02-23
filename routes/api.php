<?php

use App\Http\Controllers\C_BillController;
use App\Http\Controllers\C_IAController;
use App\Http\Controllers\C_MailController;
use App\Http\Controllers\C_NetwoorkController;
use App\Http\Controllers\C_StripeController;
use App\Http\Controllers\C_UserController;
use App\Http\Controllers\C_MailSettingsController;
use Illuminate\Support\Facades\Route;

$idRoute = '/{id}';

Route::group(['prefix' => '/auth'], function () {
    Route::name('auth.')->group(function () {
        Route::post('/register', [C_UserController::class, 'register'])->name('register');
        Route::post('/login', [C_UserController::class, 'login'])->name('login');
        Route::post('/AuthenticatedUser', [C_UserController::class, 'GetAuthenticatedUser'])->name('GetAuthenticatedUser');
    });
});


// Routes protégées par bearer token
Route::middleware('auth:sanctum')->group(function () use ($idRoute) {
    Route::group(['prefix' => '/users'], function () use ($idRoute) {
        Route::name('api.')->controller(C_UserController::class)->group(function () use ($idRoute) {
            Route::get($idRoute, 'getUser')->name('getUser');
            // Route::get('/users/{id}', [C_UserController::class, 'getUser']);
            Route::post('/sertchUser', 'sertchgetUser')->name('sertchgetUser'); // a voir
            //  Route::post('/', 'addUser')->name('addUser');
            Route::put($idRoute, 'updateUser')->name('updateUser');
            Route::delete($idRoute, 'deleteUser')->name('deleteUser');
            Route::post('/uploadlogo', 'uploadImage')->name('uploadImage');
            Route::post('/abonnementUser', 'abonnementUser')->name('abonnementUser');
        });
    });
    Route::group(['prefix' => '/post'], function () use ($idRoute) {
        Route::name('post.')->controller(C_NetwoorkController::class)->group(function () use ($idRoute) {
            Route::post('/', 'createPublishPost')->name('createPublishPost'); // envoie post sur network
            Route::post('/Facebook', 'createAndPublishPostPictureFacebook')->name('createAndPublishPostPictureFacebook');
            Route::post('/Instagrame', 'createAndPublishPostInstagramePicture')->name('createAndPublishPostInstagramePicture');
            Route::post('/Linkeding', 'createAndPublishPostPictureLinkeding')->name('createAndPublishPostPictureLinkeding');
            Route::post('/addPosts/{idUser}', 'addPosts')->name('addPosts');
            Route::post('/SearchPost/{idPost}', 'SearchPost')->name('SearchPost');
            Route::post('/listerCommentairesandLike', 'ListeCommentaireAndLikeNetwork')->name('ListeCommentaireAndLikeNetwork');
            Route::post('/listerCommentairesandLikeInstagram', 'listerCommentairesandLikeIstagram')->name('listerCommentairesandLikeIstagram'); // supprimer après juste pour test
            Route::post('/listerCommentairesandLikeLinkeding', 'listerCommentairesandLikeLinkeding')->name('listerCommentairesandLikeLinkeding'); // supprimer après juste pour test
            Route::post('/listerCommentairesandLikeFacebook', 'listerCommentairesandLikeFacebook')->name('listerCommentairesandLikeFacebook'); // supprimer après juste pour test
            Route::get('/ListePosts'.$idRoute, 'ListerPosts')->name('ListerPosts');
            Route::post('/UploadPictureNetwork', 'UploadPictureNetwork')->name('UploadPictureNetwork');
            Route::post('/AutomatisationPost', 'genererPostsAutomatiquement')->name('genererPostsAutomatiquement');
        });
    });
    Route::group(['prefix' => '/bill'], function () {
        Route::name('api.')->controller(C_BillController::class)->group(function () {
            Route::post('/generatebill', 'generateBill')->name('generateBill');
        });
    });
    Route::group(['prefix' => '/ia'], function () {
        Route::name('api.')->controller(C_IAController::class)->group(function () {
            Route::post('/generateIALocal', [C_IAController::class, 'generatprompt'])->name('generatprompt');
            Route::post('/generateIA', [C_IAController::class, 'generatpromptgemini'])->name('generatpromptgemini');
            Route::post('/generateIApicture', [C_IAController::class, 'generatPictureGPT'])->name('generatPictureGPT');
        });
    });
    Route::group(['prefix' => '/stripe'], function () use ($idRoute) {
        Route::name('stripe.')->controller(C_IAController::class)->group(function () use ($idRoute) {
            Route::post('/createPaymentIntent', [C_StripeController::class, 'createPaymentIntent']);
            Route::get('/abonnement'.$idRoute, [C_StripeController::class, 'getAbonnement']);
        });
    });

    Route::group(['prefix' => '/mail'], function () use ($idRoute) {
        Route::name('api.')->controller(C_MailController::class)->group(function () use ($idRoute) {
            Route::post('/', 'createPublishMail')->name('createPublishMail');
            Route::post('/generateMail', 'generateMail')->name('generateMail');
            Route::post('/AddMail'.$idRoute, 'AddMail')->name('AddMail');

            Route::get('/ListMailingUser'.$idRoute, 'getListMailingUser')->name('getListMailingUser'); // lister mail d un utilisateur

            Route::get('/ListMailingsendClient'.$idRoute, 'getListMailingWhithSendClients')->name('getListMailingWhithSendClients'); // liste des mail  avec liste clients
            Route::get('/SearchMailing'.$idRoute, 'SearchMailingById')->name('SearchMailingById');
            Route::put('/UpdateMailing'.$idRoute, 'updateMailing')->name('updateMailing');
            Route::delete('/DeleteMailing'.$idRoute, 'deleteMailing')->name('deleteMailing');

            Route::get('/ListDestinataireClient'.$idRoute, 'getListDestinataire')->name('getListDestinataire');
            Route::post('/AddDestinataireClient'.$idRoute, 'AddListDestinataire')->name('AddListDestinataire');
            Route::put('/UpdateDestinataireClient'.$idRoute, 'UpdateListDestinataire')->name('UpdateListDestinataire');
            Route::delete('/DeleteListDestinataire'.$idRoute, 'deleteListDestinataire')->name('deleteListDestinataire');
        });

        Route::controller(C_MailSettingsController::class)->group(function () use ($idRoute) {
            Route::get('/ListDestinataireClient'.$idRoute, 'getListDestinataire')->name('getListDestinataire');
            Route::post('/ListDestinataireEmail/', 'getListDestinataireEmail')->name('getListDestinataireEmail');
            Route::post('/AddDestinataireClient'.$idRoute, 'AddListDestinataire')->name('AddListDestinataire');
            Route::put('/UpdateDestinataireClient'.$idRoute, 'UpdateListDestinataire')->name('UpdateListDestinataire');
            Route::get('/ListDestinatairebyMail/{mailDestinataire}', 'ListDestinatairebyMail')->name('ListDestinatairebyMail');
            Route::delete('/DeleteListDestinataire'.$idRoute, 'deleteListDestinataire')->name('deleteListDestinataire');
        });
    });
});

Route::get('/test-smtp', [C_MailController::class, 'testSmtp'])->name('testSmtp');
