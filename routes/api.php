<?php

use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('login', 'Api\AuthController@login');
Route::post('register', 'Api\AuthController@register');

Route::get('logout', 'Api\AuthController@logout');

Route::group([ 'middleware' => 'jwtAuth', 'namespace' => 'Api'], function () {
    Route::post('save_user_info','AuthController@saveUserInfo');


    Route::group(['prefix' => 'posts'], function () {
        //posts
        Route::get('','PostsController@posts');
        Route::post('create','PostsController@create');
        Route::post('delete','PostsController@delete');
        Route::post('update','PostsController@update');
        Route::get('my_posts','PostsController@myPosts');

        //post comments
        Route::post('comments','CommentsController@comments');
        //like
        Route::post('like','LikesController@like');
    });

    Route::group(['prefix' => 'comments'], function () {
        //comment
        Route::post('create','CommentsController@create');
        Route::post('delete','CommentsController@delete');
        Route::post('update','CommentsController@update');
    });
});

