<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes(['verify' => true]);

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/test-connect-2bizbox', function () {
    $pdo = DB::connection('2BizBox')->getPdo();
    $all_list = $pdo->query(
        'SELECT * FROM `bb2_default`.`ab` LIMIT 0, 1000'
        )->fetchAll(\PDO::FETCH_ASSOC) ?? [];
    dd($all_list);
});

Route::get('/test-connect-t357', function () {
    $pdo = DB::connection('sqlsrv')->getPdo();
    $all_list = $pdo->query(
            'SELECT TOP (1000) [Flag]'.
            ',[StatecodeID]'.
            ',[StatecodeName]'.
            ',[TaxationID]'.
            ',[TaxationName]'.
            'FROM [CHIComp07].[dbo].[Comstate]'
        )->fetchAll(\PDO::FETCH_ASSOC) ?? [];
    dd($all_list);
});

Route::group(['prefix' => 'test0', 'namespace' => 'App\Http\Controllers\ERP'], function () {
//採購單→收料單(NTD) 可能需權限
    Route::get('logTotalRo','LogTotalRoController@index');
    Route::post('logTotalRo','LogTotalRoController@search');
    Route::post('logTotalRoExport','LogTotalRoController@export');
//採購單
    Route::get('po1','Po1Controller@index');
    Route::post('po1','Po1Controller@search');
    Route::post('po1export', 'Po1Controller@export');
//收料單
    Route::get('ro1','Ro1Controller@index');
    Route::post('ro1','Ro1Controller@search');
    Route::post('ro1export', 'Ro1Controller@export');
//工單調度報表
    Route::get('wo1','Wo1Controller@index');
    Route::post('wo1','Wo1Controller@search');
    Route::post('wo1export', 'Wo1Controller@export');
});
