<?php

use App\Http\Controllers\Api\Client\ContactController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Client API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register Client API routes for your application.
| These routes are loaded by the RouteServiceProvider with the 'api'
| middleware group and versioned with the API prefix.
|
*/

// Contact Details
Route::get('contact', [ContactController::class, 'index'])->name('contact.index');
