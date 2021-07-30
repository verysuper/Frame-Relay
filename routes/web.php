<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes(['verify' => true]);

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::group(['as' => 'erp.', 'prefix' => 'test0', 'namespace' => 'App\Http\Controllers\ERP'], function () {
    Route::any('logPoRcv', 'LogPoRcvController@search')->name('logPoRcv'); //採購單→收料單(NTD) 可能需權限
    Route::post('logPoRcvExport', 'LogPoRcvController@export');
    Route::any('po1', 'Po1Controller@search')->name('po1'); //採購單
    Route::post('po1export', 'Po1Controller@export');
    Route::any('ro1', 'Ro1Controller@search')->name('ro1'); //收料單
    Route::post('ro1export', 'Ro1Controller@export');
    Route::any('wo1', 'Wo1Controller@search')->name('wo1'); //工單調度報表
    Route::post('wo1export', 'Wo1Controller@export');
});
Route::group(['as' => 'erp.', 'prefix' => 'test1', 'namespace' => 'App\Http\Controllers\ERP'], function () {
    Route::any('upPtCost', 'TurnDataController@upPtCost')->name('upPtCost'); // 最新收料成本to零件表
});
