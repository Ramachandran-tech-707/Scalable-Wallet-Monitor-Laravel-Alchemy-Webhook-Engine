<?php

namespace App\Http\Controllers;

use App\Models\CustomVariable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\AlchemyWebhookService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WebhookAdminController extends Controller
{
    public function create(Request $request, AlchemyWebhookService $svc)
    {
        $request->validate([
            'network' => 'required|string',
            'webhook_url' => 'required|url',
            'addresses' => 'nullable|string',
            'csv_file' => 'nullable|file|mimes:csv,txt|max:10240', // 10MB
        ]);

        try {
            $addresses = [];

            // 1. Manual addresses from textarea (comma-separated)
            if ($request->filled('addresses')) {
                $manual = explode(',', $request->input('addresses'));
                $addresses = array_merge($addresses, array_map('trim', $manual));
            }

            // 2. Addresses from uploaded CSV (1 address per line)
            if ($request->hasFile('csv_file')) {
                $file = $request->file('csv_file');
                $lines = file($file->getRealPath(), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach ($lines as $line) {
                    $addresses[] = trim($line);
                }
            }

            // 3. Sanitize
            $addresses = array_unique(array_filter($addresses));

            if (empty($addresses)) {
                return redirect()->back()->with('error', 'No valid addresses provided.');
            }

            $network = $request->input('network');
            $webhookUrl = $request->input('webhook_url');

            // 4. Split into chunks of 1000
            // $chunks = array_chunk($addresses, 1000);

            $chunks = array_chunk($addresses, 500); // Instead of 1000
            $createdWebhooks = [];

            foreach ($chunks as $index => $chunk) {
                try {
                    Log::info("Creating webhook chunk " . ($index + 1) . "/" . count($chunks));
                    $data = $svc->createAddressActivityWebhook($network, $chunk, $webhookUrl);
                    $createdWebhooks[] = $data;
                    sleep(1); // 1-second delay to avoid rate limits
                }
                catch (\Exception $e) {
                    Log::error("Webhook creation failed at chunk " . ($index + 1) . ": " . $e->getMessage());
                    return redirect()->back()->with('error', "Webhook creation failed at chunk " . ($index + 1) . ": " . $e->getMessage());
                }
            }

            return redirect()->back()->with('success', count($createdWebhooks) . ' webhook(s) created successfully!');
        }
        catch (\Exception $e) {
            return redirect()->back()->with('error', 'Webhook creation process failed: ' . $e->getMessage());
        }
    }

    public function patch(Request $request, AlchemyWebhookService $svc)
    {
        $request->validate([
            'webhook_id' => 'required|string',
            'addresses_to_add' => 'nullable|string',
            'addresses_to_remove' => 'nullable|string',
        ]);

        $toAdd = array_filter(array_map('trim', explode(',', $request->addresses_to_add ?? '')));
        $toRemove = array_filter(array_map('trim', explode(',', $request->addresses_to_remove ?? '')));

        try {
            $result = $svc->patchWebhookAddresses($request->webhook_id, $toAdd, $toRemove);
            return back()->with('success', 'Webhook patched successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to patch webhook: ' . $e->getMessage());
        }
    }

    public function replace(Request $request, AlchemyWebhookService $svc)
    {
        $request->validate([
            'webhook_id' => 'required|string',
            'addresses' => 'required|string',
        ]);

        $addresses = array_filter(array_map('trim', explode(',', $request->addresses)));

        try {
            $result = $svc->replaceWebhookAddresses($request->webhook_id, $addresses);
            return back()->with('success', 'Webhook replaced successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to replace webhook: ' . $e->getMessage());
        }
    }

    public function delete(Request $request, AlchemyWebhookService $svc)
    {
        $request->validate([
            'webhook_id' => 'required|string',
        ]);

        try {
            $data = $svc->deleteWebhook($request->input('webhook_id'));
            return redirect()->back()->with('success', 'Webhook deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete webhook: ' . $e->getMessage());
        }
    }

    public function manage()
    {
        return view('admin.webhooks.manage');
    }

    public function showVariableWebhookForm()
    {
        $variables = CustomVariable::latest()->get();
        return view('admin.variables.create_webhook', compact('variables'));
    }

    public function createVariableWebhook(Request $request, AlchemyWebhookService $svc)
    {
        $validated = $request->validate([
            'network' => 'required|string',
            'webhook_url' => 'required|url',
            'variable_name' => 'required|string', // e.g., "userWallets"
        ]);

        try {
            $network = $validated['network'];
            $webhookUrl = $validated['webhook_url'];
            $variableName = $validated['variable_name'];

            // Call service method
            $response = $svc->createVariableWebhook($network, $variableName, $webhookUrl);
            $webhookId = $response['data']['id'] ?? null;

            Log::info("Custom variable webhook created successfully", [
                'network' => $network,
                'variable' => $variableName,
                'webhook_url' => $webhookUrl,
                'response' => $response,
                'webhookId' => $webhookId
            ]);
           
            return redirect()->back()->with('success', "Custom variable webhook created successfully! ID: " . ($webhookId ?? 'N/A'));
        }
        catch (\Exception $e) {
            Log::error("Alchemy API client error during webhook creation: " . $e->getMessage());
            return redirect()->back()->with('error', 'Custom webhook creation failed: ' . $e->getMessage());
        }
    }

}
