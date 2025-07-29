<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    ZamowienieController,
    AutomatController,
    StrataController,
    ExportController,
    RegisterController,
    WiadomoscController,
    Auth\ForgotPasswordController,
    Auth\ResetPasswordController,
    LoginController,
    WsadController,
    ProduktController
};
use App\Models\Wsad;

Route::redirect('/', '/login'); // dynamiczne przekierowanie na login

Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');

Route::get('/capybara', function () {
    return view('capybara');
})->name('capybara.show');

Route::get('/password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');

Route::middleware(['auth'])->group(function () {
    Route::get('/welcome', [AutomatController::class, 'index'])->name('welcome');

    Route::resource('zamowienia', ZamowienieController::class)->only(['create', 'store', 'index']);
    Route::get('/zamowienia/produkcja/nowe', [ZamowienieController::class, 'createProdukcja'])->name('zamowienia.produkcja.create');
    Route::get('/zamowienia/archiwum', [ZamowienieController::class, 'archiwum'])->name('zamowienia.archiwum');
    Route::get('/zamowienia/{zamowienie}', [ZamowienieController::class, 'show'])->name('zamowienia.show');
    Route::get('/zamowienia/podsumowanie/dzien/{date?}', [ZamowienieController::class, 'podsumowanieDnia'])->name('zamowienia.podsumowanie.dzien');
    Route::get('/zamowienia/podsumowanie/tydzien/{date?}', [ZamowienieController::class, 'podsumowanieTygodnia'])->name('zamowienia.podsumowanie.tydzien');
    Route::get('/zamowienia/podsumowanie/miesiac/{month?}', [ZamowienieController::class, 'podsumowanieMiesiaca'])->name('zamowienia.podsumowanie.miesiac');
    Route::get('/zamowienia/podsumowanie/rok/{year?}', [ZamowienieController::class, 'podsumowanieRoku'])->name('zamowienia.podsumowanie.rok');
    Route::get('/zamowienia/{id}/xlsx', [ZamowienieController::class, 'pobierzZamowienieXlsx'])->name('zamowienia.xlsx');
    Route::get('/zamowienia/{id}/csv', [ZamowienieController::class, 'pobierszZamowienieCsv'])->name('zamowienia.csv');
    Route::post('/zamowienie/zloz', [ZamowienieController::class, 'store'])->name('zloz.zamowienie');

    Route::get('/straty/archiwum', [StrataController::class, 'archiwum'])->name('straty.archiwum');
    Route::get('/straty/podsumowanie/dzien/{date?}', [StrataController::class, 'podsumowanieDnia'])->name('straty.podsumowanie.dzien');
    Route::get('/straty/podsumowanie/tydzien/{date?}', [StrataController::class, 'podsumowanieTygodnia'])->name('straty.podsumowanie.tydzien');
    Route::get('/straty/podsumowanie/miesiac/{month?}', [StrataController::class, 'podsumowanieMiesiaca'])->name('straty.podsumowanie.miesiac');
    Route::get('/straty/podsumowanie/rok/{year?}', [StrataController::class, 'podsumowanieRoku'])->name('straty.podsumowanie.rok');

    Route::get('/wsady/podsumowanie/dzien/{date?}', [WsadController::class, 'podsumowanieDnia'])->name('wsady.podsumowanie.dzien');
    Route::get('/wsady/podsumowanie/tydzien/{date?}', [WsadController::class, 'podsumowanieTygodnia'])->name('wsady.podsumowanie.tydzien');
    Route::get('/wsady/podsumowanie/miesiac/{month?}', [WsadController::class, 'podsumowanieMiesiaca'])->name('wsady.podsumowanie.miesiac');
    Route::get('/wsady/podsumowanie/rok/{year?}', [WsadController::class, 'podsumowanieRoku'])->name('wsady.podsumowanie.rok');


    Route::get('/register', [RegisterController::class, 'show'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);

    Route::resource('straty', StrataController::class, ['parameters' => ['straty' => 'strata']])->only(['index', 'create', 'store', 'show']);


    Route::get('/export/{typ}/{zakres}/{od?}/{format?}', [ExportController::class, 'unifiedExport'])->where(['typ' => 'zamowienia|straty|wsady', 'zakres' => 'dzien|tydzien|miesiac|rok'])->name('export.unified.day');
    Route::get('/export/{typ}/{zakres}/{format}/{od}/{do}', [ExportController::class, 'unifiedExport'])->where(['typ' => 'zamowienia|straty|wsady', 'zakres' => 'zakres'])->name('export.unified.range');
    Route::get('/export/zamowienie/{zamowienie_id}/{format}', [ExportController::class, 'exportPojedynczeZamowienie'])->name('export.zamowienie');


    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/wsady/archiwum', [WsadController::class, 'archiwum'])->name('wsady.archiwum');
    Route::get('/wsady/automat/{automat_id}', [WsadController::class, 'index'])->name('wsady.byAutomat');
    Route::post('/wsady/{produkt_id}/{automat_id}/decrease', [WsadController::class, 'decrease'])->name('wsady.decrease');
    Route::delete('/wsady/{produkt_id}/{automat_id}/delete', [WsadController::class, 'delete'])->name('wsady.delete');
    Route::resource('wsady', WsadController::class);

    Route::get('/produkty/niewlasne/formularz-nowe-zamowienie', [ProduktController::class, 'formularzNoweZamowienie'])->name('produkty.zamowienie.formularz');
    Route::get('/produkty/niewlasne/nowe-zamowienie', [ProduktController::class, 'noweZamowienie'])->name('produkty.zamowienie.nowe');
    Route::get('/produkty/niewlasne/zamowienie/{zamowienieId}', [ProduktController::class, 'edytujZamowienie'])->name('produkty.zamowienie.edytuj');
    Route::post('/produkty/niewlasne/zamowienie/zapisz', [ProduktController::class, 'zapiszZamowienie'])->name('produkty.zamowienie.zapisz');

    Route::get('/produkty/wlasne/nowy', [ProduktController::class, 'createWlasny'])->name('produkty.create.wlasny');
    Route::post('/produkty/wlasne/nowy', [ProduktController::class, 'storeWlasny'])->name('produkty.store.wlasny');

    Route::get('/wiadomosc', [WiadomoscController::class, 'create'])->name('wiadomosc.create');
    Route::post('/wiadomosc', [WiadomoscController::class, 'store'])->name('wiadomosc.store');

    Route::post('/produkty/niewlasne/nowy', [ProduktController::class, 'storeNiewlasny'])->name('produkty.store.niewlasny');
    Route::get('/produkty/niewlasne/nowy', [ProduktController::class, 'createNiewlasny'])->name('produkty.create.niewlasny');

});
