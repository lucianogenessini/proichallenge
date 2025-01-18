<?php

use App\Enums\Role;
use App\Http\Controllers\API\Auth\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'api', 'namespace' => 'App\Http\Controllers\API'], function ($router) {

    Route::group(['prefix' => 'auth', 'namespace' => 'Auth'], function ($router) {
        Route::post('login', [AuthController::class, 'login'])->name('auth.login');
        Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    });
    
    //public access
    Route::get('articles', 'ArticleController@index')->name('articles.index');

    Route::group(['middleware' => ['auth:sanctum']], function ($router) {
        
        // Admin-only routes
        Route::group(['middleware' => ['role:'.Role::SUPER_ADMIN_ROLE->value]], function ($router) {
            Route::resource('articles', 'ArticleController')->only('store', 'destroy');
        });

        Route::put('articles/{article}', 'ArticleController@update')->name('articles.update');
    });
});

