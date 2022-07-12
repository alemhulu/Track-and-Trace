<?php

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
    Route::get('/location', function () { return view('location.index'); })->name('location');
    Route::get('/organization', function () { return view('organization.index'); })->name('organization');
    Route::get('/warehouse', function () { return view('warehouse.index'); })->name('warehouse');
    Route::get('/book', function () { return view('book.index'); })->name('book');
    Route::get('/print-order', function () { return view('print-order.index'); })->name('print-order');
    Route::get('/route', function () { return view('route.index'); })->name('route');
    Route::get('/distribution', function () { return view('distribution.index'); })->name('distribution');
    Route::get('/trace', function () { return view('trace.index'); })->name('trace');

    Route::group(['prefix' => 'student', 'as' => 'student.'], function() {
        Route::resource('lessons', \App\Http\Controllers\Students\LessonController::class);
    });

   Route::group(['prefix' => 'teacher', 'as' => 'teacher.'], function() {
       Route::resource('courses', \App\Http\Controllers\Teachers\CourseController::class);
   });
   
    Route::group([ 'prefix' => 'admin', 'as' => 'admin.'], function() {
        Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
    });
});
