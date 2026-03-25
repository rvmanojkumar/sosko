<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function showLoginForm()
{
    
    if (Auth::check()) {
        $user = Auth::user();
        if ($user->hasRole(['admin', 'super-admin'])) {
            return redirect('/admin');
        } elseif ($user->hasRole('vendor')) {
            return redirect('/vendor');
        }
        return redirect('/');
       
    }
    dd('showLoginForm');
    return view('auth.admin-login');
}

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'role' => 'sometimes|in:admin,vendor'
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');
        $requestedRole = $request->input('role', 'admin');

        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();

            // Role check
            if ($requestedRole === 'admin' && !$user->hasRole(['admin', 'super-admin'])) {
                Auth::logout();
                throw ValidationException::withMessages([
                    'email' => 'You do not have admin access.',
                ]);
            }

            if ($requestedRole === 'vendor' && !$user->hasRole('vendor')) {
                Auth::logout();
                throw ValidationException::withMessages([
                    'email' => 'You do not have vendor access.',
                ]);
            }

            $request->session()->regenerate();

            if ($user->hasRole(['admin', 'super-admin'])) {
                return redirect()->intended('/admin');
            } elseif ($user->hasRole('vendor')) {
                return redirect()->intended('/vendor');
            }

            return redirect()->intended('/');
        }

        throw ValidationException::withMessages([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}