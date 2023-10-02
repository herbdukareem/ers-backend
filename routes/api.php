<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EnroleeVisitController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Transformers\UtilResource;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('/login', function() {
    return new UtilResource("You must be logged in to do that!", true, 401);    
})->name('login');


// routes/api.php

Route::prefix('auth')->group(function () {      
    Route::post('login',[AuthController::class,'login']);      
});

Route::group(['middleware'=>'auth:api'], function () {
        
    // Users routes    
    
    Route::post('/meds/save', [EnroleeVisitController::class, 'medsSave']);
    Route::post('/enrolee-visits/bulk', [EnroleeVisitController::class, 'storeBulk']);
    Route::get('/enrolee-visits', [EnroleeVisitController::class, 'fetchVisits']);
    Route::post('/enrolee-visits', [EnroleeVisitController::class, 'fetchVisits']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::get('/enrolees-data/{id}', [AuthController::class, 'enrolees']);
    Route::post('/users', [UserController::class, 'store']);  
});
