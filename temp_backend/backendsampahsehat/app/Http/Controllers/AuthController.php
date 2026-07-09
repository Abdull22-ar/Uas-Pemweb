<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    // =========================================================================
    // SHOW FORM
    // =========================================================================

    /**
     * Tampilkan halaman login.
     * Redirect ke dashboard jika sudah login.
     */
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('admin.auth.login');
    }

    // =========================================================================
    // AUTHENTICATE
    // =========================================================================

    /**
     * Proses login admin.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string', 'min:6'],
        ], [
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
            'password.min'      => 'Password minimal 6 karakter.',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            // Regenerate session untuk mencegah session fixation attack
            $request->session()->regenerate();

            return redirect()
                ->intended(route('dashboard'))
                ->with('success', 'Selamat datang, ' . Auth::user()->name . '!');
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors([
                'email' => 'Email atau password yang Anda masukkan salah.',
            ]);
    }

    // =========================================================================
    // LOGOUT
    // =========================================================================

    /**
     * Proses logout admin.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        // Invalidate & regenerate token untuk keamanan
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('info', 'Anda telah berhasil keluar dari sistem.');
    }
}
