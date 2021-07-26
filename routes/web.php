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

Route::get('test0/logTotalRo','App\Http\Controllers\ERP\LogTotalRoController@index');
Route::post('test0/logTotalRo','App\Http\Controllers\ERP\LogTotalRoController@search');
Route::post('test0/logTotalRoExport','App\Http\Controllers\ERP\LogTotalRoController@export');
