<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\EanCode;
use App\Http\Controllers\EanController;
use App\Http\Controllers\ProduktController;



Route::middleware(['auth'])->group(function () {
    Route::post('/check-ean', [EanController::class, 'checkEan']); // Endpoint do sprawdzania EAN
    
});
