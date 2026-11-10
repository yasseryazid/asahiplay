<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'user' => $user->toAsahi(),
            'children' => $user->children()->orderBy('id')->get()->map->toAsahi()->values(),
            'best' => $user->children->mapWithKeys(fn ($c) => [$c->id => (object) $c->bestStars()]),
        ]);
    }

    /** Simpan isi Ruang Orang Tua. */
    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'parent' => ['nullable', 'string', 'max:20'],
            'msg' => ['nullable', 'string', 'max:200'],
            'promise' => ['nullable', 'string', 'max:100'],
            'sound' => ['sometimes', 'boolean'],
            'consent' => ['sometimes', 'accepted'],
        ]);

        $user = $request->user();
        $user->fill([
            'parent_call' => array_key_exists('parent', $data) ? $data['parent'] : $user->parent_call,
            'note_message' => array_key_exists('msg', $data) ? $data['msg'] : $user->note_message,
            'note_promise' => array_key_exists('promise', $data) ? $data['promise'] : $user->note_promise,
            'sound' => $data['sound'] ?? $user->sound,
        ]);
        if (! empty($data['consent']) && ! $user->consent_at) {
            $user->consent_at = now();
        }
        $user->save();

        return response()->json(['user' => $user->toAsahi()]);
    }
}
