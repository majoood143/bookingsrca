<?php

namespace App\Http\Controllers;

use App\Models\Kiosk;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

// Called by the kiosk's native Android app roughly every 30-60s to report
// whether its USB reader/printer are currently connected — surfaced in
// Filament (KioskResource) so staff can see hardware health remotely.
class KioskHeartbeatController extends Controller
{
    public function store(Request $request, Kiosk $kiosk): JsonResponse
    {
        $data = $request->validate([
            'reader_connected'  => 'required|boolean',
            'printer_connected' => 'required|boolean',
            'app_version'       => 'nullable|string|max:50',
        ]);

        $kiosk->recordHeartbeat(
            (bool) $data['reader_connected'],
            (bool) $data['printer_connected'],
            $data['app_version'] ?? null,
        );

        return response()->json(['ok' => true]);
    }
}
