<?php

// use BeyondCode\LaravelWebSockets\Apps\AppProvider;

use App\Events\SendMessage;
use BeyondCode\LaravelWebSockets\DashboardLogger;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Carbon\Carbon;


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
    return view('chat-app-example', [
        'port' => env('LARAVEL_WEBSOCKETS_PORT'),
        'host' => env('LARAVEL_WEBSOCKETS_HOST'),
        'authEndpoint' => "api/sockets/connect",
        'logChannel' => DashboardLogger::LOG_CHANNEL_PREFIX,
        'apps' => config('websockets.apps'),
    ]);
});


Route::post('/chat/send', function(Request $request){
    $message = $request->input('message', null);
    $name = $request->input('name', 'Anonymous');

    $time = (new DateTime(now()))->format(DateTime::ATOM);
    if($name == null){
        $name = "Anonymous";
    }


    SendMessage::dispatch($name, $message, $time);
});