<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CalendarController;
 

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
    return view('welcome');
});

//Route::get('hello', [WelcomeController::class,"Hello"]);

Route::get('signin', [AuthController::class,"signin"]);
Route::get('/callback', [AuthController::class,"callback"]);
Route::get('/signout', [AuthController::class,"signout"]);
Route::get('/calendar', [CalendarController::class,"calendar"]);
Route::get('/calendar/new', [CalendarController::class,"getNewEventForm"]);
Route::get('/calendar/new', [CalendarController::class,"createNewEvent"]);
//Route::get('/signin', 'AuthController@signin');
/*Route::get('/callback', 'AuthController@callback');
Route::get('/signout', 'AuthController@signout');
Route::get('/calendar', 'CalendarController@calendar');
Route::get('/calendar/new', 'CalendarController@getNewEventForm');
Route::post('/calendar/new', 'CalendarController@createNewEvent');*/
