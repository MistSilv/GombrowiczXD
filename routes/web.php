<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ZamowienieController;
use App\Http\Controllers\AutomatController;
use App\Http\Controllers\StrataController;
use App\Http\Controllers\ExportController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/zamowienia/nowe', [ZamowienieController::class, 'create'])->name('zamowienia.create');
Route::post('/zamowienia', [ZamowienieController::class, 'store'])->name('zamowienia.store');
Route::resource('zamowienia', ZamowienieController::class)->only(['create', 'store', 'index']);

Route::get('/zamowienia', [ZamowienieController::class, 'index'])->name('zamowienia.index');
Route::get('/zamowienia/archiwum', [ZamowienieController::class, 'archiwum'])->name('zamowienia.archiwum');
Route::get('/zamowienia/{zamowienie}', [ZamowienieController::class, 'show'])->name('zamowienia.show');

Route::get('/zamowienia/podsumowanie/dzien/{date?}', [ZamowienieController::class, 'podsumowanieDnia'])->name('zamowienia.podsumowanie.dzien');
Route::get('/zamowienia/podsumowanie/tydzien/{date?}', [ZamowienieController::class, 'podsumowanieTygodnia'])->name('zamowienia.podsumowanie.tydzien');
Route::get('/zamowienia/podsumowanie/miesiac/{month?}', [ZamowienieController::class, 'podsumowanieMiesiaca'])->name('zamowienia.podsumowanie.miesiac');
Route::get('/zamowienia/podsumowanie/rok/{year?}', [ZamowienieController::class, 'podsumowanieRoku'])->name('zamowienia.podsumowanie.rok');

Route::get('/', [AutomatController::class, 'index']);

Route::resource('straty', StrataController::class)->only(['create', 'store', 'show']);

Route::get('/export/{zakres}/{date?}/{format?}', [ExportController::class, 'exportZamowienia'])->name('zamowienia.export');


