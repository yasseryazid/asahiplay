<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class AuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')
            ->scopes(['openid', 'profile', 'email'])
            ->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        try {
            $g = Socialite::driver('google')->user();
        } catch (Throwable $e) {
            report($e);
            return redirect('/')->with('auth_error', 'Gagal masuk dengan Google. Coba lagi, ya.');
        }

        // Cari berdasarkan google_id dulu, lalu email (jika akun pernah dibuat dengan cara lain).
        $user = User::where('google_id', $g->getId())->first()
            ?? User::where('email', $g->getEmail())->first()
            ?? new User();

        $user->fill([
            'name' => $user->name ?: ($g->getName() ?: $g->getEmail()),
            'email' => $g->getEmail(),
            'google_id' => $g->getId(),
            'avatar' => $g->getAvatar(),
        ]);
        if (! $user->exists) {
            $user->email_verified_at = now();   // email Google sudah terverifikasi
        }
        $user->save();

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return redirect()->intended('/');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
