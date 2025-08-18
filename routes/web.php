<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\PasswordUpdateController;

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

Route::get('{any}', function () {
    return view('app');
})->where('any', '.*');

/* Route::middleware('auth')->group(function () {
    Route::get('/profile/change-password', [PasswordUpdateController::class, 'edit'])->name('password.edit');
    Route::post('/profile/change-password', [PasswordUpdateController::class, 'update'])->name('password.update');
}); */