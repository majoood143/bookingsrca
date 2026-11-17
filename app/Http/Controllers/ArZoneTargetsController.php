<?php

namespace App\Http\Controllers;

use App\Models\ArZone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ArZoneTargetsController extends Controller
{
    /** Stores the .mind file the admin's browser compiled from the zone's target images. */
    public function store(Request $request, ArZone $zone): JsonResponse
    {
        abort_unless($request->user()->can('update', $zone->destination), 403);

        $data = $request->validate([
            'file' => ['required', 'file', 'max:51200'],
            'item_ids' => ['required', 'array', 'max:'.ArZone::MAX_TARGETS],
            'item_ids.*' => ['integer'],
        ]);

        // Items changed while the browser compiled: indexes would point at the wrong items.
        $ids = array_map('intval', $data['item_ids']);
        abort_unless($ids === $zone->targetItems()->pluck('id')->all(), 422, __('explore.compile.out_of_date'));

        $zone->storeCompiledTargets($data['file'], $ids);

        return response()->json(['compiled_at' => $zone->targets_compiled_at->toIso8601String()]);
    }
}
