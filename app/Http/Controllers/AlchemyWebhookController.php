<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Jobs\ProcessUsdtDeposit;

class AlchemyWebhookController extends Controller
{
    public function __invoke(Request $request)
    {
        // Verify a shared secret
        // if ($request->header('X-Webhook-Secret') !== config('services.alchemy.webhook_secret')) {
        //     abort(403, 'Unauthorized');
        // return response()->json(['error' => 'Unauthorized'], 403);
        // }

        // Log full webhook payload for auditing
        Log::info('Alchemy Webhook Payload Received', [
            'payload' => $request->all()
        ]);

        $events = $request->input('event.activity', []);

        foreach ($events as $activity) {
            Log::info('Dispatching TX activity: ' . json_encode($activity));
            ProcessUsdtDeposit::dispatch($activity);
        }

        return response()->json(['status' => 'ok']);
    }
}
