<?php

use App\Http\Controllers\Api\V1\VisitController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EnroleeVisitController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\UserController;
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
Route::get('/login', [LoginController::class,'index'])->name('login-page');
Route::get('/logout', [LoginController::class,'logout'])->name('logout');
Route::post('/login',[LoginController::class,'login'])->name('login-post');
Route::post('/change_password', [UserController::class, 'changePassword']);
Route::group(["middleware"=>['web','auth']],function(){
    Route::get('/', function () {
        return customView('ers_report');
    })->name('Enrollee-Visits');
    Route::post('/assign_role', [UserController::class, 'assignRole']);
    Route::post('/assign_permission', [UserController::class, 'assignPermission']);
    
    Route::get('/medicals', function () { return view('medicals'); })->name('Medicals');
    Route::get('/users', function () { return view('users'); })->name('users');
    
    Route::get('/ers-dashboard', function () { return customView('ers_dashboard'); })->name('ers-dashboard');
    Route::get('/excutive-dashboard', function () { return customView('executive_dashboard'); })->name('executive_dashboard');
    Route::get('/ers-report', function () { return customView('ers_report'); })->name('ers_report');
   
});


Route::prefix('v1')->group(function(){    
    Route::get('/reports/total-encounters', [VisitController::class, 'totalEncounters']);
    
    Route::post('/executive/analytics', [VisitController::class, 'ExecutiveAnalysis']);
    Route::post('ers/reports/analytics', [VisitController::class, 'EnrolleesAnalysis']);
    Route::post('/reports/medical/analytics', [VisitController::class, 'medicalsBillsReport']);
    Route::get('/reports/encounters-last-month', [VisitController::class, 'encountersLastMonth']);
    Route::get('/reports/encounters-by-quarter/{year}', [VisitController::class, 'encountersByQuarter']);
    Route::post('/top_accessed_services', [VisitController::class, 'topAccessedService']);
    Route::post('/enrollee_by_category', [VisitController::class, 'enrolleeByCategory']);
    
    Route::post('/ecounters', [EnroleeVisitController::class, 'index']);
});

Route::get('email_verify/{verify?}',[AuthController::class, 'verifyEmail']);
