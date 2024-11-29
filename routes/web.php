<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GoogleAuthController;
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


//google login

Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('google-auth');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callBackGoogle']);

// Route::get('{any?}', function() {
//     return view('welcome');
// })->where('any', '.*');