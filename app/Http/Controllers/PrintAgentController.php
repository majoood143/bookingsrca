<?php

namespace App\Http\Controllers;

use App\Models\BookingSetting;
use App\Models\PrintJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

// Polled by the on-site print agent (print-agent/print-agent.php) over HTTPS.
// The cloud app can't reach the printer directly — it's on a private on-site
// LAN — so it queues rendered ESC/POS bytes as a PrintJob and the agent fetches
// + delivers them locally, then acks the result back here.
class PrintAgentController extends Controller
{
    private const MAX_ATTEMPTS = 3;

    public function next(Request $request): JsonResponse
    {
        $this->authorizeAgent($request);

        $job = DB::transaction(function () {
            $job = PrintJob::where('status', 'pending')
                ->orWhere(function ($query) {
                    $query->where('status', 'claimed')
                        ->where('claimed_at', '<', now()->subMinutes(2));
                })
                ->oldest()
                ->lockForUpdate()
                ->first();

            if (!$job) {
                return null;
            }

            $job->update([
                'status' => 'claimed',
                'claimed_at' => now(),
                'attempts' => $job->attempts + 1,
            ]);

            return $job;
        });

        BookingSetting::set('printer.agent_last_seen_at', now()->toDateTimeString());

        if (!$job) {
            return response()->json(null, 204);
        }

        return response()->json([
            'id' => $job->id,
            'type' => $job->type,
            'payload_base64' => base64_encode(Storage::disk('local')->get($job->payload_path)),
        ]);
    }

    public function ack(Request $request, PrintJob $job): JsonResponse
    {
        $this->authorizeAgent($request);

        $data = $request->validate([
            'status' => 'required|in:printed,failed',
            'error' => 'nullable|string|max:2000',
        ]);

        // Give a transient failure (printer briefly offline) another chance —
        // only give up once attempts are exhausted, so a jammed printer
        // doesn't silently lose the job on the first retry-able failure.
        $isTerminal = $data['status'] === 'printed' || $job->attempts >= self::MAX_ATTEMPTS;

        $job->update([
            'status' => $isTerminal ? $data['status'] : 'pending',
            'printed_at' => $data['status'] === 'printed' ? now() : null,
            'error' => $data['error'] ?? null,
        ]);

        if ($isTerminal) {
            Storage::disk('local')->delete($job->payload_path);
        }

        return response()->json(['ok' => true]);
    }

    private function authorizeAgent(Request $request): void
    {
        $token = (string) BookingSetting::get('printer.agent_token', '');
        $provided = (string) $request->bearerToken();

        if ($token === '' || !hash_equals($token, $provided)) {
            abort(401, 'Invalid or missing agent token.');
        }
    }
}
