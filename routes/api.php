<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
Route::any('/logger', function(Request $request) {
	Log::debug($request->all());
	
	return response()->json(['error' => true, 'message' => 'Test'], 400);
});

Route::prefix('auth')->group(function() {
	Route::middleware('jwt.auth')->group(function() {
		Route::get('user', function (Request $request) {
				return ['data' => $request->user()];
		});
		Route::post('logout', 'Api\AuthController@logout');
	});
	Route::post('login', 'Api\AuthController@login');
	Route::get('refresh', 'Api\AuthController@refresh');
	Route::post('register', 'Api\AuthController@register');
});
Route::post('subscribe', 'Api\NotificationController@subscribe');

Route::middleware('jwt.auth')->group(function() {
	Route::prefix('videos')->group(function() {
		Route::post('getByMonitor', 'Api\VideosController@getByMonitor');
		Route::post('getByMonitorId', 'Api\VideosController@getByMonitorId');
	});

	Route::prefix('monitors')->group(function() {
		Route::any('get', 'Api\MonitorsController@getById');
		Route::get('getAll', 'Api\MonitorsController@getAll');
		Route::post('create', 'Api\MonitorsController@create');
		Route::delete('delete', 'Api\MonitorsController@delete');
	});
	
	Route::prefix('admin')->middleware('role:admin')->group(function() {
		Route::prefix('notify')->group(function() {
			Route::post('create', 'Api\NotificationController@create');
			Route::get('getAll', 'Api\NotificationController@getAll');
		});
		
		Route::prefix('users')->group(function() {
			Route::get('get', 'Api\Admin\UsersController@getById');
			Route::get('getAll', 'Api\Admin\UsersController@getAll');
			Route::post('create', 'Api\Admin\UsersController@create');
			Route::delete('delete/{id}', 'Api\Admin\UsersController@delete');
		});
		
		Route::prefix('servers')->group(function() {
			Route::get('getAll', 'Api\Admin\ServerController@getAll');
			Route::post('create', 'Api\Admin\ServerController@create');
			Route::get('getById', 'Api\Admin\ServerController@getById');
		});
		
		Route::prefix('admins')->group(function() {
			Route::get('get', 'Api\Admin\AdminController@getById');
			Route::get('getAll', 'Api\Admin\AdminController@getAll');
			Route::post('create', 'Api\Admin\AdminController@create');
			Route::delete('delete', 'Api\Admin\AdminController@delete');
		});
	});
});



