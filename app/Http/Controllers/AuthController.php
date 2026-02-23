<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\LandingPageSetting;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Show the login form
     */
    public function showLoginForm()
    {
        if (Auth::guard('admin')->check()) {
            return redirect('/admin/dashboard');
        }

        if (Auth::guard('dosen')->check()) {
            return redirect('/dosen/dashboard');
        }

        if (Auth::guard('mahasiswa')->check()) {
            return redirect('/mahasiswa/dashboard');
        }

        $branding = LandingPageSetting::first();

        return view('auth.login', [
            'branding' => $branding,
        ]);
    }

    /**
     * Handle login for different user types
     */
    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string', // email, NIP, atau NPM
            'password' => 'required',
        ]);

        $identifier = $request->input('login');
        $password = $request->input('password');

        // Main throttle keys
        $baseKey = Str::transliterate(Str::lower($identifier).'|'.$request->ip());
        $lockoutKey = $baseKey . ':lockout';
        $attemptsKey = $baseKey . ':attempts';

        // 1. Check if user is currently locked out
        if (RateLimiter::tooManyAttempts($lockoutKey, 1)) {
            $seconds = RateLimiter::availableIn($lockoutKey);
            throw ValidationException::withMessages([
                'login' => ['Terlalu banyak percobaan login. Silakan coba lagi dalam ' . $seconds . ' detik.'],
            ]);
        }

        $guards = ['admin', 'dosen', 'mahasiswa'];

        foreach ($guards as $guard) {
            $fields = [];
            if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) { $fields[] = 'email'; }
            if (in_array($guard, ['admin', 'dosen'], true)) { $fields[] = 'nip'; }
            if ($guard === 'mahasiswa') { $fields[] = 'npm'; }
            $fields = array_unique($fields);

            foreach ($fields as $field) {
                if (Auth::guard($guard)->attempt([$field => $identifier, 'password' => $password])) {
                    // Success: Clear all throttle data
                    RateLimiter::clear($lockoutKey);
                    RateLimiter::clear($attemptsKey);
                    
                    $request->session()->regenerate();
                    return match($guard) {
                        'admin' => redirect()->intended('/admin/dashboard'),
                        'dosen' => redirect()->intended('/dosen/dashboard'),
                        'mahasiswa' => redirect()->intended('/mahasiswa/dashboard'),
                        default => redirect()->intended('/')
                    };
                }
            }
        }

        // 2. Login Failed: Increment attempts
        RateLimiter::hit($attemptsKey, 60); // Failure count window is 60s
        $attempts = RateLimiter::attempts($attemptsKey);
        $maxAttempts = 5;

        if ($attempts >= $maxAttempts) {
            // 3. Trigger Lockout on the 5th failure
            RateLimiter::hit($lockoutKey, 60); // Lock for exactly 60s
            RateLimiter::clear($attemptsKey);  // Reset counter for after lockout
            
            $seconds = RateLimiter::availableIn($lockoutKey);
            throw ValidationException::withMessages([
                'login' => ['Terlalu banyak percobaan login. Silakan coba lagi dalam ' . $seconds . ' detik.'],
            ]);
        }

        $remaining = $maxAttempts - $attempts;
        throw ValidationException::withMessages([
            'login' => ['Username atau password salah. Sisa percobaan: ' . $remaining],
        ]);
    }

    /**
     * Log the user out of the application
     */
    public function logout(Request $request)
    {
        $guards = ['web', 'admin', 'dosen', 'mahasiswa'];
        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                Auth::guard($guard)->logout();
            }
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
