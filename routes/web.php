<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Container\BindingResolutionException;
use App\Http\Controllers\Administrator;
use App\Http\Controllers;
use App\Http\Controllers\RegisterController;

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




Route::get('/admin/dashboard', 'DashboardController@index');



//Buku
Route::get('/admin/buku', 'bukuController@read');
Route::get('/admin/buku/add', 'bukuController@add');
Route::post('/admin/buku/create', 'bukuController@create');
Route::get('/admin/buku/edit/{id}', 'bukuController@edit');
Route::post('/admin/buku/update/{id}', 'bukuController@update');
Route::get('/admin/buku/delete/{id}', 'bukuController@delete');
Route::get('/admin/buku/buku/{id}', 'bukuController@buku');
Route::get('/admin/buku/cari', 'BukuController@cari');
Route::get('/admin/buku/print_buku', 'bukuController@print_buku');
Route::get('/admin/buku/print', 'bukuController@print');



Route::get('/home', 'HomeController@index')->name('home');
