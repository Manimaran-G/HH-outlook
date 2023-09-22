<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\MicrosoftGraphController;
use App\Http\Controllers\OutlookCalendarController;
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

Route::get('signin', [AuthController::class,"signin1"]);
Route::get('/callback1', [AuthController::class,"callback"]);
Route::get('/signout', [AuthController::class,"signout"]);
Route::get('/calendar', [CalendarController::class,"calendar"]);
Route::get('/calendar/new', [CalendarController::class,"getNewEventForm"]);
Route::get('/calendar/new', [CalendarController::class,"createNewEvent"]);

Route::get('/addevent111', [OutlookCalendarController::class,'createEvent1']);

Route::get('/hello', [OutlookCalendarController::class,'index']);
Route::get('/callback', [OutlookCalendarController::class,'callback']);
Route::post('/add-event', [OutlookCalendarController::class,'createEvent']);
Route::patch('/update-event', [OutlookCalendarController::class,'updateEvent']);
Route::post('/cancel-event', [OutlookCalendarController::class,'cancelEvent']);
Route::delete('/delete-event', [OutlookCalendarController::class,'deleteEvent']);
Route::post('/get-event', [OutlookCalendarController::class,'listEvents']);
Route::post('/refresh-event', [OutlookCalendarController::class,'refreshEvents']);




Route::get('/microsoft-graph', [MicrosoftGraphController::class, 'index']);
Route::get('callback11111111', [MicrosoftGraphController::class, 'callback']);
Route::get('/calendar11', [OutlookCalendarController::class, 'getCalendarEvents']);

//Route::get('/signin', 'AuthController@signin');
/*Route::get('/callback', 'AuthController@callback');
Route::get('/signout', 'AuthController@signout');
Route::get('/calendar', 'CalendarController@calendar');
Route::get('/calendar/new', 'CalendarController@getNewEventForm');
Route::post('/calendar/new', 'CalendarController@createNewEvent');*/
