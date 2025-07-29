<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\EanCode;
use App\Http\Controllers\EanController;
use App\Http\Controllers\ProduktController;
use App\Http\Controllers\TelegramController;

Route::post('/check-ean', [EanController::class, 'checkEan']); // Endpoint do sprawdzania EAN

Route::get('/produkty/search', [ProduktController::class, 'search'])->name('produkty.search');

Route::post('/telegram-callback', [TelegramController::class, 'handleCallback']);
