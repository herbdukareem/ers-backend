<?php

use App\Http\Controllers\AuthController;
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
})->name('Enrollee-Visits');

Route::get('/medicals', function () {
    return view('medicals');
})->name('Medicals');

Route::get('/dashboard', function () {
    return view('welcome');
})->name('Dashboard');


Route::get('email_verify/{verify?}',[AuthController::class, 'verifyEmail']);
