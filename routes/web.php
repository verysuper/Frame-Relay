<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes(['verify' => true]);

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::group(['as' => 'erp.', 'prefix' => 'test0', 'namespace' => 'App\Http\Controllers\ERP'], function () {
    Route::any('logTotalRo','LogTotalRoController@search')->name('logTotalRo'); //採購單→收料單(NTD) 可能需權限
    Route::post('logTotalRoExport','LogTotalRoController@export');
    Route::any('po1','Po1Controller@search')->name('po1'); //採購單
    Route::post('po1export', 'Po1Controller@export');
    Route::any('ro1','Ro1Controller@search')->name('ro1'); //收料單
    Route::post('ro1export', 'Ro1Controller@export');
    Route::any('wo1','Wo1Controller@search')->name('wo1'); //工單調度報表
    Route::post('wo1export', 'Wo1Controller@export');
});
