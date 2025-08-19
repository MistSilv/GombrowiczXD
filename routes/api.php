<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\EanCode;
use App\Http\Controllers\EanController;
use App\Http\Controllers\ProduktController;
use App\Http\Controllers\TelegramController;
use App\Http\Controllers\NetworkController;
use Illuminate\Support\Facades\Log;


Route::post('/check-ean', [EanController::class, 'checkEan']); 
Route::post('/check-ean-firmowe', [EanController::class, 'checkEan1']);


Route::get('/produkty/search', [ProduktController::class, 'search'])->name('produkty.search');

Route::post('/telegram-callback', [TelegramController::class, 'handleCallback']);

Route::get('/network/check', [NetworkController::class, 'check']);
Route::get('/network/ip', [NetworkController::class, 'getClientIp']);


Route::get('/produkty/template/{id}', [ProduktController::class, 'getTemplateProdukty']);

Route::get('/produkty/wlasny-template/{id}', [ProduktController::class, 'getWlasnyTemplateProdukty']);