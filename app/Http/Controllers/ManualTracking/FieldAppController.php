<?php

namespace App\Http\Controllers\ManualTracking;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Services\ManualTracking\FieldSyncService;
use App\Services\ManualTracking\ManualTrackingException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * The offline field app. The page itself is cached on the phone by the service worker;
 * these endpoints are only reached when a connection is available.
 */
class FieldAppController extends Controller
{
    public function show(Request $request): View
    {
        return view('field.app', [
            'organizations' => Organization::query()->accessibleBy($request->user())->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function pack(Request $request, FieldSyncService $field): JsonResponse
    {
        $organization = Organization::query()->accessibleBy($request->user())->findOrFail((int) $request->query('organization'));

        try {
            return response()->json($field->pack($request->user(), $organization));
        } catch (ManualTrackingException $exception) {
            return response()->json(['message' => $exception->getMessage()], 403);
        }
    }

    public function sync(Request $request, FieldSyncService $field): JsonResponse
    {
        $actions = $request->validate([
            'actions' => 'required|array|max:500',
            'actions.*' => 'array',
        ])['actions'];

        return response()->json([
            'results' => array_map(fn ($action) => $field->process($request->user(), $action), $actions),
            'csrf' => csrf_token(),
        ]);
    }
}
