<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function show()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]); // Walidacja danych wejściowych

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/welcome');
        } // Jeśli uwierzytelnianie nie powiodło się, przekieruj z błędem

        return back()->withErrors([
            'email' => 'Podane dane są nieprawidłowe.',
        ])->onlyInput('email'); // Zwróć błąd, jeśli dane logowania są nieprawidłowe
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login'); // Wylogowanie użytkownika i przekierowanie na stronę logowania
    }
}