<?php

use App\Http\Controllers\DistributionController;
use App\Http\Controllers\RoleController;
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

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    Route::get('/dashboard', function () { return view('dashboard'); })->name('dashboard');
    Route::get('/location', function () { return view('main.location.index'); })->name('location');
    Route::get('/organization', function () { return view('main.organization.index'); })->name('organization');
    Route::get('/warehouse', function () { return view('main.warehouse.index'); })->name('warehouse');
    Route::get('/book', function () { return view('main.book.index'); })->name('book');
    Route::get('/print-order', function () { return view('main.print-order.index'); })->name('print-order');
    Route::get('/route', function () { return view('main.route.index'); })->name('route');
    Route::get('/distribution', function () { return view('main.distribution.index'); })->name('distribution');
    Route::get('/trace', function () { return view('main.trace.index'); })->name('trace');

    Route::resource('roles', RoleController::class);
    Route::resource('users', UserController::class);
    Route::resource('destribution-details', DistributionController::class);


});
