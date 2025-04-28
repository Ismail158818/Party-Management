<?php

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\EventApiController;
use App\Http\Controllers\Api\UserApiController;
use App\Http\Controllers\Api\CommentApiController;
use App\Http\Middleware\CheckAdmin;

use Illuminate\Support\Facades\Route;

Route::controller(AuthApiController::class)->group(function () {
    Route::post('register', 'register');
    Route::post('login', 'login');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::controller(EventApiController::class)->group(function () {
        Route::post('index', 'index');
        Route::post('store', 'store')->middleware(CheckAdmin::class);
        Route::post('show', 'show');
        Route::post('destroy', 'destroy')->middleware(CheckAdmin::class);
    });
    Route::controller(UserApiController::class)->group(function () {
        Route::post('toggleJoin','toggleJoin');
        Route::post('show','show');
        Route::post('updateUsers','update');
        Route::post('destroyUser','destroy');
        Route::post('searchUsers', 'search'); // عرض المستخدمين
        Route::post('users_event','users_event'); // عرض الحدثات الخا��ة بالمستخدم


      

    });
    Route::controller(CommentApiController::class)->group(function () {
        Route::post('storeComment', 'store'); // إضافة تعليق
        Route::post('indexComments', 'index'); // عرض تعليقات الحدث
        Route::post('destroyComment', 'destroy'); // حذف تعليق
        Route::post('updateComment', 'update'); // تعديل تعليق  
        

    });
    

});
