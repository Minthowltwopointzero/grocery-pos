<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route(Auth::user()->isAdmin() ? 'dashboard' : 'pos.index');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            if (! Auth::user()->is_active) {
                Auth::logout();
                return back()->withErrors(['username' => 'This account has been deactivated.']);
            }

            // Admins land on the Dashboard; cashiers go straight to POS
            // Checkout since Dashboard/Sales History are admin-only.
            $defaultRoute = Auth::user()->isAdmin() ? 'dashboard' : 'pos.index';

            return redirect()->intended(route($defaultRoute));
        }

        return back()->withErrors([
            'username' => 'Invalid username or password.',
        ])->onlyInput('username');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
