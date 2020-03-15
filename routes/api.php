<?php

use Illuminate\Http\Request;

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

Route::prefix('auth')->group(function() {
	Route::middleware('jwt.auth')->group(function() {
		Route::get('user', function (Request $request) {
				return $request->user();
		});
		Route::post('logout', 'Api\AuthController@logout');
	});
	Route::post('login', 'Api\AuthController@login');
	Route::get('refresh', 'Api\AuthController@refresh');
	Route::post('register', 'Api\AuthController@register');
});

Route::prefix('videos')->middleware('jwt.auth')->group(function() {
	Route::post('getByMonitor', 'Api\VideosController@getByMonitor');
	Route::post('getByMonitorId', 'Api\VideosController@getByMonitorId');
});

Route::prefix('monitors')->middleware('jwt.auth')->group(function() {
	Route::get('getAll', 'Api\MonitorsController@getAll');
	Route::post('getById', 'Api\MonitorsController@getById');
});

Route::prefix('admin')->group(function() {
	Route::post('login', 'Api\Admin\AuthController@login');
	Route::prefix('server')->group(function() {
		Route::get('getAll', 'Api\Admin\ServerController@getAll');
		Route::get('getById', 'Api\Admin\ServerController@getById');
	});
});
