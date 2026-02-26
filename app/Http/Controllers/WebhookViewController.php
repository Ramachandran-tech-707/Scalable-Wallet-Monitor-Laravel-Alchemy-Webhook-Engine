<?php

namespace App\Http\Controllers;

use App\Models\CustomVariable;
use App\Models\UserWallet;
use App\Services\AlchemyWebhookService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookViewController extends Controller
{
    public function dashboard(AlchemyWebhookService $svc)
    {
        try {
            // Wallets & Custom Variables
            $walletsCount = UserWallet::count();
            $variablesCount = CustomVariable::count();
            $recentVariables = CustomVariable::latest()->take(5)->get();

            // Fetch Webhooks
            $webhooks = $svc->listWebhooks();
            // Log::info('Raw Alchemy webhooks:', ['webhooks' => $webhooks]);

            $webhooksCount = is_array($webhooks) ? count($webhooks) : 0;

            // Normalize recent 5 webhooks
            $recentWebhooks = collect($webhooks)->map(function ($wh) {
                return [
                    'id'           => $wh['id'] ?? null,
                    'event_type'   => $wh['webhook_type'] ?? 'Unknown Event',
                    'createdAt'    => isset($wh['time_created']) 
                                        ? \Carbon\Carbon::createFromTimestampMs($wh['time_created']) 
                                        : null,
                    'status'       => isset($wh['is_active']) ? ($wh['is_active'] ? 'Active' : 'Inactive') : 'Unknown',
                    'webhook_url'  => $wh['webhook_url'] ?? '',
                    'deactivation' => $wh['deactivation_reason'] ?? '',
                ];
            })->sortByDesc('createdAt')->take(5);

            // Log::info('Processed recent webhooks:', ['recentWebhooks' => $recentWebhooks->toArray()]);

            return view('admin.dashboard', compact(
                'webhooksCount',
                'walletsCount',
                'variablesCount',
                'recentWebhooks',
                'recentVariables'
            ));
        }
        catch (\Exception $e) {
            Log::error('Dashboard fetch failed: ' . $e->getMessage());
            
            return view('admin.dashboard', [
                'webhooksCount' => 0,
                'walletsCount' => UserWallet::count(),
                'variablesCount' => CustomVariable::count(),
                'recentWebhooks' => collect([]),
                'recentVariables' => CustomVariable::latest()->take(5)->get(),
                'error' => 'Failed to fetch webhook data from Alchemy.'
            ]);
        }
    }

    public function manage()
    {
        return view('admin.webhooks.manage');
    }

    public function history(Request $request, AlchemyWebhookService $svc)
    {
        try {
            $search = $request->input('search');

            $webhooks = $svc->listWebhooks();

            if ($search) {
                $webhooks = array_filter($webhooks, function ($webhook) use ($search) {
                    return str_contains(strtolower($webhook['id']), strtolower($search))
                        || str_contains(strtolower($webhook['name']), strtolower($search))
                        || str_contains(strtolower($webhook['network']), strtolower($search));
                });
            }

            return view('admin.webhooks.history', [
                'webhooks' => $webhooks,
                'search' => $search
            ]);
        }
        catch (\Exception $e) {
            Log::error('Failed to fetch team webhook history: ' . $e->getMessage());
            return back()->with('error', 'Failed to fetch team webhook history.');
        }
    }

    public function showAddresses(Request $request, string $webhookId, AlchemyWebhookService $svc)
    {
        try {
            // Validate inputs
            $validated = $request->validate([
                'limit' => 'nullable|integer|min:1|max:500',
                'after' => 'nullable|string',
                'pageKey' => 'nullable|string',
            ]);

            $filters = [
                'webhook_id' => $webhookId,
                'limit'      => $validated['limit'] ?? 100,
                'after'      => $validated['after'] ?? '',
                'pageKey'    => $validated['pageKey'] ?? '',
            ];

            $response = $svc->getWebhookAddresses($filters);

            $addresses = $response['data'] ?? [];
            $pagination = $response['pagination']['cursors'] ?? [];
            $totalCount = $response['pagination']['total_count'] ?? 0;

            return view('admin.webhooks.addresses', compact(
                'webhookId',
                'addresses',
                'pagination',
                'totalCount',
                'filters'
            ));
        }
        catch (\Throwable $e) {
            Log::error("Failed to fetch addresses for webhook ID {$webhookId}: " . $e->getMessage());

            return redirect()
                ->route('webhooks.history')
                ->with('error', 'Failed to fetch webhook addresses: ' . $e->getMessage());
        }
    }
}
