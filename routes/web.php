<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/



// APPLICATION LINKS

Route::get('/', 'UserController@nothing');

Route::post('/app/register', 'UserController@register');

Route::post('/app/update/profile', 'UserController@updateProfile');

Route::post('/app/register/game', 'UserController@registerGame');

Route::post('/app/update/game', 'UserController@updateGame');

Route::post('/app/login', 'UserController@login');

Route::get('/app/choices/{mid}', 'UserController@choices');

Route::post('/app/choice/{mid}/{id}', 'UserController@choice');

Route::get('/app/matchs/{mid}', 'UserController@matchs');

Route::get('/app/teste', 'UserController@teste');
