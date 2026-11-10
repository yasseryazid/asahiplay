<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Child;
use App\Models\Play;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PlayController extends Controller
{
    /** Buku Kenangan satu anak, terbaru dulu. */
    public function index(Request $request, Child $child): JsonResponse
    {
        ChildController::authorizeChild($request, $child);

        $plays = $child->plays()->latest('played_at')->limit(200)->get();

        return response()->json([
            'plays' => $plays->map->toAsahi()->values(),
            'best' => (object) $child->bestStars(),
        ]);
    }

    /** Simpan hasil satu sesi main. */
    public function store(Request $request, Child $child): JsonResponse
    {
        ChildController::authorizeChild($request, $child);

        $data = $request->validate([
            'lv' => ['required', Rule::in(Child::GRADES)],
            'tk' => ['required', 'string', 'max:40', 'regex:/^[A-Za-z0-9_]+$/'],
            'stars' => ['required', 'integer', 'between:0,3'],
            'items' => ['nullable', 'integer', 'between:0,500'],
            'miss' => ['nullable', 'integer', 'between:0,500'],
            'score' => ['nullable', 'integer', 'between:0,1000'],
            'together' => ['boolean'],
            'by' => ['nullable', 'array', 'size:2'],
            'by.*' => ['integer', 'between:0,500'],
        ]);

        $play = $child->plays()->create([
            'level' => $data['lv'],
            'topic' => $data['tk'],
            'stars' => $data['stars'],
            'items' => $data['items'] ?? 0,
            'misses' => $data['miss'] ?? 0,
            'score' => $data['score'] ?? null,
            'together' => $data['together'] ?? false,
            'by_kid' => $data['by'][0] ?? 0,
            'by_parent' => $data['by'][1] ?? 0,
            'played_at' => now(),
        ]);

        return response()->json(['play' => $play->toAsahi()], 201);
    }

    /** Stempel dari orang tua. */
    public function update(Request $request, Play $play): JsonResponse
    {
        ChildController::authorizeChild($request, $play->child);

        $data = $request->validate(['stamp' => ['nullable', 'integer', 'between:0,3']]);
        $play->update(['stamp' => $data['stamp']]);

        return response()->json(['play' => $play->toAsahi()]);
    }
}
