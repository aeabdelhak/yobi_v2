<?php

use App\Http\Controllers\audioController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\cardController;
use App\Http\Controllers\colorPalleteController;
use App\Http\Controllers\colorsController;
use App\Http\Controllers\FilesController;
use App\Http\Controllers\imagesController;
use App\Http\Controllers\LandinPageController;
use App\Http\Controllers\offerController;
use App\Http\Controllers\orderController;
use App\Http\Controllers\permissionController;
use App\Http\Controllers\shapesController;
use App\Http\Controllers\sizesController;
use App\Http\Controllers\storeAccessController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\tawsilixController;
use App\Http\Controllers\userResultsController;
use App\Http\Controllers\vercelController;
use Illuminate\Support\Facades\Route;

Route::middleware('refToken')->group(function () {
    Route::controller(AuthController::class)->group(function () {
        Route::post('login', 'login');
        Route::post('register', 'register');
        Route::post('logout', 'logout');
        Route::get('user', 'user');
        Route::get('user/ungrantedSearch', 'ungrantUsersSearch');
        Route::post('refresh', 'refresh');
        Route::get('user/all', 'all');
        Route::post('user/avatar', 'avatarUpload');
        Route::put('user/name', 'editName');
        Route::put('user/password', 'editpassword');
        Route::put('user/status', 'changeStatus');
        Route::get('user/{id}', 'get');
        Route::delete('user/{id}', 'delete');

    });
    Route::controller(StoreController::class)->group(function () {
        Route::post('store/new', 'newStore');
        Route::get('store/select ', 'select');
        Route::get('store/current ', 'current');
        Route::get('store/get ', 'only');
        Route::get('store/all', 'allStores');
        Route::delete('store/delete', 'delete');
        Route::post('store/edit', 'edit');
        Route::get('store/client', 'client');
        Route::get('store/dashboard', 'dashboard');

    });
    Route::controller(colorPalleteController::class)->group(function () {
        Route::post('pallete/new', 'newPallete');
        Route::get('pallete/get ', 'get');
        Route::get('pallete/all', 'All');
        Route::delete('pallete/delete', 'delete');
        Route::post('pallete/edit', 'edit');
    });
    Route::controller(LandinPageController::class)->group(function () {
        Route::get('landing/client', 'client');
        Route::get('landing/all', 'all');
        Route::get('landing/only', 'only');
        Route::get('landing', 'get');
        Route::post('landing', 'newLanding');
        Route::delete('landing', 'delete');
        Route::post('landing/edit', 'edit');
    });
    Route::controller(shapesController::class)->group(function () {
        Route::get('shape', 'get');
        Route::post('shape', 'newShape');
        Route::delete('shape', 'delete');
        Route::put('shape/edit', 'edit');
        Route::post('shape/toggleStatus', 'toggleStatus');
    });
    Route::controller(colorsController::class)->group(function () {
        Route::get('color', 'get');
        Route::delete('color', 'delete');
        Route::post('color/edit', 'edit');
        Route::post('color', 'newColor');
        Route::post('color/toggleStatus', 'toggleStatus');
    });
    Route::controller(sizesController::class)->group(function () {
        Route::get('size', 'get');
        Route::post('size', 'newSize');
        Route::put('size', 'edit');
        Route::delete('size', 'delete');
        Route::post('size/toggleStatus', 'toggleStatus');

    });
    Route::controller(cardController::class)->group(function () {
        Route::get('card', 'get');
        Route::post('card', 'newCard');
        Route::put('card', 'edit');
        Route::delete('card', 'delete');
    });
    Route::controller(audioController::class)->group(function () {
        Route::get('audio', 'get');
        Route::post('audio', 'newAudio');
        Route::delete('audio', 'delete');
        Route::put('audio', 'edit');
    });
    Route::controller(FilesController::class)->group(function () {
        Route::get('path', 'download');
    });
    Route::controller(permissionController::class)->group(function () {
        Route::post('permission/allow', 'allow');
        Route::post('permission/forbid', 'forbid');
    });
    Route::controller(orderController::class)->group(function () {
        Route::post('order/edit', 'edit');
        Route::post('order/submit', 'newOrder');
        Route::get('order/paginate', 'getOrders');
        Route::get('order/statistics', 'getStatistics');
        Route::post('order/status', 'changeStatus');
        Route::post('order/push', 'pushToDelivery');
        Route::post('order/delete', 'delete');
        Route::get('order/delayed', 'getDelayedTotoday');
        Route::get('order/isSyncing', 'isSyncing');
        Route::get('order/history/{id}', 'history');
        Route::get('order/name/{id}', 'getOrderName');
        Route::get('order/{id}', 'getOrder');
    });
    Route::controller(offerController::class)->group(function () {
        Route::post('offer', 'newOffer');
        Route::put('offer', 'edit');
        Route::delete('offer', 'delete');
        Route::post('offer/assign', 'assign');
        Route::post('offer/activate', 'setActive');
        Route::post('offer/desactivate', 'setInActive');
    });
    Route::controller(vercelController::class)->group(function () {
        Route::post('test/{domain}', 'domainAdd');
    });
    Route::controller(tawsilixController::class)->group(function () {
        Route::get('/tawsilix/cities', 'cities');
        Route::get('/tawsilix/update', 'updateOrderStatus');
        Route::get('/tawsilix/check/{id}', 'checkStatus');
    });

    Route::controller(userResultsController::class)->group(function () {
        Route::post('newResult', 'newResult');
        Route::delete('newResult/{id}', 'delete');

    });
    Route::controller(imagesController::class)->group(function () {
        Route::post('image', 'newImage');
        Route::delete('image/{id}', 'delete');

    });
    Route::controller(storeAccessController::class)->group(function () {
        Route::post('store/access/grant', 'grant');
        Route::delete('store/access/disgrant', 'delete');

    });
});
Route::fallback(function () {
    return response()->json(null, 404);
});