<?php

use App\Http\Controllers\Api\VisitController;
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
    return view('visits');
})->name('Enrollee-Visits');

Route::get('/medicals', function () {
    return view('medicals');
})->name('Medicals');

Route::get('/dashboard', function () {
    return view('dashboard');
})->name('Dashboard');


Route::get('/reports/total-encounters', [VisitController::class, 'totalEncounters']);
Route::post('/reports/analytics', [VisitController::class, 'EnrolleesAnalysis']);
Route::post('/reports/medical/analytics', [VisitController::class, 'medicalsBillsReport']);
Route::get('/reports/encounters-last-month', [VisitController::class, 'encountersLastMonth']);
Route::get('/reports/encounters-by-quarter/{year}', [VisitController::class, 'encountersByQuarter']);
Route::get('/test', [VisitController::class, 'test']);

Route::get('email_verify/{verify?}',[AuthController::class, 'verifyEmail']);
