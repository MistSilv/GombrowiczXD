<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

use App\Http\Controllers\ZamowienieController;
use App\Http\Controllers\AutomatController;
use App\Http\Controllers\StrataController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;

//publiczne trasy dostępne bez autoryzacji
// Widok logowania
Route::view('/', 'login');
Route::view('/login', 'login')->name('login');

// Obsługa logowania
Route::post('/login', [LoginController::class, 'login']);

// Reset hasła - formularz i obsługa
Route::get('/password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');

// Trasy dostępne tylko dla zalogowanych użytkowników
Route::middleware(['auth'])->group(function () {

    // Strona startowa po zalogowaniu
    Route::get('/welcome', [AutomatController::class, 'index'])->name('welcome');

   //zarządzanie zamówieniami
    Route::prefix('zamowienia')->name('zamowienia.')->group(function () {

        Route::get('/nowe', [ZamowienieController::class, 'create'])->name('create');
        Route::post('/', [ZamowienieController::class, 'store'])->name('store');
        Route::get('/', [ZamowienieController::class, 'index'])->name('index');
        Route::get('/archiwum', [ZamowienieController::class, 'archiwum'])->name('archiwum');
        Route::get('/{zamowienie}', [ZamowienieController::class, 'show'])->name('show');

        // Podsumowania zamówień
        Route::get('/podsumowanie/dzien/{date?}', [ZamowienieController::class, 'podsumowanieDnia'])->name('podsumowanie.dzien');
        Route::get('/podsumowanie/tydzien/{date?}', [ZamowienieController::class, 'podsumowanieTygodnia'])->name('podsumowanie.tydzien');
        Route::get('/podsumowanie/miesiac/{month?}', [ZamowienieController::class, 'podsumowanieMiesiaca'])->name('podsumowanie.miesiac');
        Route::get('/podsumowanie/rok/{year?}', [ZamowienieController::class, 'podsumowanieRoku'])->name('podsumowanie.rok');
    });

    //zarządzanie stratami
    Route::prefix('straty')->name('straty.')->group(function () {

        Route::get('/', [StrataController::class, 'index'])->name('index');
        Route::get('/nowe', [StrataController::class, 'create'])->name('create');
        Route::post('/', [StrataController::class, 'store'])->name('store');
        Route::get('/{strata}', [StrataController::class, 'show'])->name('show');

        // Podsumowania strat
        Route::get('/podsumowanie/dzien/{date?}', [StrataController::class, 'podsumowanieDnia'])->name('podsumowanie.dzien');
        Route::get('/podsumowanie/tydzien/{date?}', [StrataController::class, 'podsumowanieTygodnia'])->name('podsumowanie.tydzien');
        Route::get('/podsumowanie/miesiac/{month?}', [StrataController::class, 'podsumowanieMiesiaca'])->name('podsumowanie.miesiac');
        Route::get('/podsumowanie/rok/{year?}', [StrataController::class, 'podsumowanieRoku'])->name('podsumowanie.rok');
    });

    //eksport danych
    Route::prefix('export')->name('export.')->group(function () {
        Route::get('/zamowienia/{zakres}/{date?}/{format?}', [ExportController::class, 'exportZamowienia'])->name('zamowienia');
        Route::get('/straty/{zakres}/{date?}/{format?}', [ExportController::class, 'exportStraty'])->name('straty');
    });

    //rejestracja nowych użytkowników
    Route::get('/register', [RegisterController::class, 'show'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);

    //wylogowanie
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});
