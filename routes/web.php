<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\AdminAuth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('front.app');
});

Route::get('/check', function () {
    return view('welcome');
});



Route::get('/backRoute', [AuthController::class, 'loadLogin']);

// Route::get('/index', [FrontEndController::class, 'index'])->name('index');


// //login-logout
Route::get('/register', [AuthController::class, 'loadRegister']);
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::get('/login', [AuthController::class, 'loadLogin']);

Route::get('/logout', [AuthController::class, 'logout']);

Route::post('/login', [AuthController::class, 'login'])->name('login');

// //forgot-password

Route::get('/forgot', [AuthController::class, 'loadForgot']);
Route::post('/forgot', [Authcontroller::class, 'forgot'])->name('forgot');
Route::get('/reset/{email}/{token}', [AuthController::class, 'getReset']);
Route::post('/reset/{email}/{token}', [AuthController::class, 'postReset']);

//Global Api
Route::get('/api/auth/role', [AuthController::class, 'getAuthenticatedUserRole']);
Route::get('/api/getMyid',[AuthController::class, 'getMyid']);

//google login

Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('google-auth');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callBackGoogle']);





//Admin
Route::group(['prefix' => 'api/_admin', 'middleware' => ['web', 'isAdmin']], function () {
    //users
    // Route::get('/dashboard', [AdminController::class, 'dashboard']);
    Route::get('/getUsers', [AdminController::class, 'getUsers']);

    Route::patch('/{id}/changeRole', [AdminController::class, 'changeRole']);

    Route::get('/getDisease', [AdminController::class, 'getDisease']);
    Route::post('/updateDiseases', [AdminController::class, 'updateDiseases']);

    Route::post('/upload_symptom/{id}',[AdminController::class,'upload_symptom']);

    Route::get('/get_symptoms/{id}',[AdminController::class,'getSymptoms']);
});


//Doctor
Route::group(['prefix' => 'api/_doctor', 'middleware' => ['web', 'isDoctor']], function () {
    // Route::get('/dashboard', [DoctorController::class, 'dashboard']);

});

//User
Route::group(['prefix' => 'api/_user', 'middleware' => ['web', 'isUser']], function () {
    // Route::get('/dashboard', [UserController::class, 'dashboard']);

    Route::get('/getQuestions',[UserController::class,'getQuestions']);

    Route::post('/textProcessing',[UserController::class,'testProcessing']);
    Route::get('/getQuicktest',[UserController::class,'getQuicktest']);

});

// Route::get('{any?}', function() {
//     return redirect('/login');
// })->where('any', '.*');

Route::get('{view}', ApplicationController::class)->where('view', '(.*)');
