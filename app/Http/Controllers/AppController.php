<?php

namespace App\Http\Controllers;

use App\Models\QuestionBank;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AppController extends Controller
{
    /** Satu halaman Asahi. Data awal disuntikkan ke window.ASAHI supaya tidak perlu request tambahan. */
    public function show(Request $request): View
    {
        $user = $request->user();

        $boot = [
            'server' => true,
            'csrf' => csrf_token(),
            'user' => $user?->toAsahi(),
            'children' => $user ? $user->children()->orderBy('id')->get()->map->toAsahi()->values() : [],
            'best' => $user
                ? $user->children->mapWithKeys(fn ($c) => [$c->id => (object) $c->bestStars()])
                : (object) [],
            'banks' => QuestionBank::where('is_public', true)->pluck('slug'),
            'authError' => session('auth_error'),
        ];

        return view('app', ['boot' => $boot]);
    }

    public function privacy(): View
    {
        return view('privacy');
    }
}
