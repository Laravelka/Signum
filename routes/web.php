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

Route::get('/{any}', function() {
    return view('welcome');
})->where('any', '.*');

/*Route::any('firebase-cloud-messaging-push-scope', function() {
	return ['test' => true];	
});

/*
Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
*/
