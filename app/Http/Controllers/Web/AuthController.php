<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors([
                'email' => 'Email atau password yang Anda masukkan salah.',
            ])->onlyInput('email');
        }

        if ($user->status === 'pending') {
            return back()->withErrors([
                'email' => 'Akun pengelola Anda masih berstatus PENDING menunggu verifikasi dari Admin.',
            ])->onlyInput('email');
        }

        if ($user->status === 'suspended') {
            return back()->withErrors([
                'email' => 'Akun Anda telah dinonaktifkan oleh administrator.',
            ])->onlyInput('email');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        if ($user->isAdmin()) {
            return redirect()->intended(route('admin.dashboard'));
        } elseif ($user->isPengelola()) {
            return redirect()->intended(route('pengelola.dashboard'));
        }

        return redirect()->intended(route('home'));
    }

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', 'in:penyewa,pengelola'],
        ]);

        $status = ($validated['role'] === 'pengelola') ? 'pending' : 'active';

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'status' => $status,
            'email_verified_at' => now(),
        ]);

        if ($status === 'pending') {
            return redirect()->route('login')->with('warning', 'Pendaftaran pengelola berhasil! Akun Anda sedang menunggu persetujuan (verifikasi) dari Admin Platform sebelum dapat login.');
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('home')->with('success', 'Selamat datang! Pendaftaran Anda berhasil.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Anda telah berhasil logout.');
    }
}
