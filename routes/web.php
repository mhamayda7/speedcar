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
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\CaptainController;
use App\Http\Controllers\SuppliersController;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/privacy_policy', function () {
    return view('privacy_policy');
});


Route::get('captain',[CaptainController::class,'view_register']);
Route::post('captain/register',[CaptainController::class,'register'])->name('register_captain');


Route::get('suppliers/login',[SuppliersController::class,'showLogin'])->name('showLoginForm');
Route::post('suppliers/login',[SuppliersController::class,'customLogin'])->name('supplier-login');

Route::get('suppliers/dashboard',[SuppliersController::class,'dashboard'])->name('dashboard');

Route::get('search-driver',[SuppliersController::class,'add_fund_page'])->name('search_driver');

Route::post('get_driver',[SuppliersController::class,'get_driver'])->name('get_driver');

Route::post('add_fund/{id}',[SuppliersController::class,'add_fund'])->name('add_fund');

Route::get('billing',[SuppliersController::class,'billing'])->name('billing');
