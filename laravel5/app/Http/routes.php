<?php

//use Illuminate\Routing\Route;
/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });
	Route::get('/', 'SiteController@index');
// 	Route::get('/', 'SiteController@lotteryuser');
// 	Route::get('/lottery', 'LotteryController@index');
// 	Route::get('/lottery', 'LotteryController@create');
// 	Route::get('/lottery', 'LotteryController@show');
// 	Route::get('/lottery', 'LotteryController@');
	Route::resource('lottery','LotteryController');
