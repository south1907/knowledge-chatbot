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

Route::get('/', function () {
    return view('welcome');
});


Route::prefix('api')->group(function () {
    Route::prefix('xsmb')->group(function () {
	    Route::get('', 'XSMBController@index')->name('index');
	    Route::get('answer', 'XSMBController@answer')->name('answer');
	});

});
