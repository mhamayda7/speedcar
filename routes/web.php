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

use App\Http\Controllers\CaptainController;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/privacy_policy', function () {
    return view('privacy_policy');
});


Route::get('captain',[CaptainController::class,'view_register']);
Route::post('captain/register',[CaptainController::class,'register'])->name('register_captain');
