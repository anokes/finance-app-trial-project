<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

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

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

require __DIR__.'/auth.php';

Auth::routes();

Route::get('/dashboard', [App\Http\Controllers\HomeController::class, 'index'])->name('dashboard');

//routes for CRUD functionality
Route::get('/show', [App\Http\Controllers\BalanceItemsController::class, 'show']);
Route::post('/submit', [App\Http\Controllers\BalanceItemsController::class, 'store']);
Route::post('/update', [App\Http\Controllers\BalanceItemsController::class, 'update']);
Route::post('/delete', [App\Http\Controllers\BalanceItemsController::class, 'delete']);
