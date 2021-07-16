<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

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
