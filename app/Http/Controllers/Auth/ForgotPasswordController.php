<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    public function showLinkRequestForm()
    {
        return view('auth.passwords.email');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);  // Walidacja, aby upewnić się, że email jest podany i jest poprawny

        $status = Password::sendResetLink(
            $request->only('email')
        ); //link do resetowania hasła

        return $status === Password::RESET_LINK_SENT  // Sprawdza, czy link do resetowania hasła został wysłany
                    ? back()->with(['status' => __($status)])
                    : back()->withErrors(['email' => __($status)]);
    }
}