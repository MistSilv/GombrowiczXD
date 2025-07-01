<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\EanCode;
use App\Http\Controllers\EanController;


Route::post('/check-ean', [EanController::class, 'checkEan']); // Endpoint do sprawdzania EAN


