<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Child;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class ChildController extends Controller
{
    public const MAX_CHILDREN = 6;

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_if($user->children()->count() >= self::MAX_CHILDREN, 422, 'Maksimal ' . self::MAX_CHILDREN . ' anak per akun.');

        $child = $user->children()->create($this->validated($request));

        return response()->json(['child' => $child->toAsahi()], 201);
    }

    public function update(Request $request, Child $child): JsonResponse
    {
        $this->authorizeChild($request, $child);
        $child->update($this->validated($request, partial: true));

        return response()->json(['child' => $child->toAsahi()]);
    }

    public function destroy(Request $request, Child $child): Response
    {
        $this->authorizeChild($request, $child);
        $child->delete();   // plays ikut terhapus (cascade)

        return response()->noContent();
    }

    private function validated(Request $request, bool $partial = false): array
    {
        $req = $partial ? 'sometimes' : 'required';

        return $request->validate([
            'name' => [$req, 'string', 'min:1', 'max:30'],
            'grade' => [$req, Rule::in(Child::GRADES)],
        ]);
    }

    /** Anak milik keluarga lain diperlakukan seolah tidak ada. */
    public static function authorizeChild(Request $request, Child $child): void
    {
        abort_unless($child->user_id === $request->user()->id, 404);
    }
}
