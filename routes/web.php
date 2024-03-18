<?php

use App\Http\Controllers\Api\V1\VisitController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LoginController;
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
Route::group(["middleware"=>['web','auth']],function(){});
Route::get('/', function () {
    return view('visits');
})->name('Enrollee-Visits');
Route::get('/login', function () { return view('login');})->name('login-page');
Route::post('/login',[LoginController::class,'login'])->name('login-post');


Route::get('/medicals', function () {
    return view('medicals');
})->name('Medicals');

Route::get('/ers-dashboard', function () { return customView('ers_dashboard'); })->name('ers-dashboard');
Route::get('/excutive-dashboard', function () { return customView('executive_dashboard'); })->name('executive_dashboard');


Route::prefix('v1')->group(function(){    
    Route::get('/reports/total-encounters', [VisitController::class, 'totalEncounters']);
    
    Route::post('/executive/analytics', [VisitController::class, 'ExecutiveAnalysis']);
    Route::post('/reports/analytics', [VisitController::class, 'EnrolleesAnalysis']);
    Route::post('/reports/medical/analytics', [VisitController::class, 'medicalsBillsReport']);
    Route::get('/reports/encounters-last-month', [VisitController::class, 'encountersLastMonth']);
    Route::get('/reports/encounters-by-quarter/{year}', [VisitController::class, 'encountersByQuarter']);
    Route::get('/test', [VisitController::class, 'test']);
});

Route::get('email_verify/{verify?}',[AuthController::class, 'verifyEmail']);
