<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class AlchemyWebhookService
{
    protected Client $client;
    protected string $token;
    protected string $baseUrl;

    public function __construct()
    {
        $this->token = config('services.alchemy.token');
        $this->baseUrl = rtrim(config('services.alchemy.base_url'), '/'); // Ensure no trailing slash

        // Log::info('Alchemy Client initializing with:', [
        //     'base_url' => $this->baseUrl,
        //     'token_snippet' => substr($this->token, 0, 5) . '...',
        // ]);

        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'headers' => [
                'X-Alchemy-Token' => $this->token,
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    public function createAddressActivityWebhook(string $network, array $addresses, string $webhookUrl): array
    {
        $payload = [
            'network' => $network,
            'webhook_type' => 'ADDRESS_ACTIVITY',
            'addresses' => $addresses,
            'webhook_url' => $webhookUrl,
        ];

        // Log::info('Creating webhook with:', $payload);

        try {
            $response = $this->client->post('api/create-webhook', [
                'json' => $payload,
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            // Log::info('Webhook created:', $data);

            return $data['data'] ?? [];
        } catch (RequestException $e) {
            $body = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : 'No response body';
            Log::error('Alchemy Webhook creation failed: ' . $e->getMessage() . ' - Response: ' . $body);
            throw $e;
        }
    }

    public function patchWebhookAddresses(string $webhookId, array $toAdd = [], array $toRemove = []): array
    {
        $payload = [
            'webhook_id' => $webhookId,
            'addresses_to_add' => $toAdd,
            'addresses_to_remove' => $toRemove,
        ];

        try {
            $response = $this->client->patch('api/update-webhook-addresses', [
                'json' => $payload
            ]);

            $body = json_decode($response->getBody()->getContents(), true);
            // Log::info('PATCH webhook update successful', $body);

            return $body['data'] ?? [];
        } catch (RequestException $e) {
            $body = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : 'No response body';
            Log::error('Alchemy Webhook PATCH update failed: ' . $e->getMessage() . ' - Response: ' . $body);
            throw $e;
        }
    }

    public function replaceWebhookAddresses(string $webhookId, array $addresses): array
    {
        $payload = [
            'webhook_id' => $webhookId,
            'addresses' => $addresses
        ];

        try {
            $response = $this->client->put('api/update-webhook-addresses', [
                'json' => $payload
            ]);

            $body = json_decode($response->getBody()->getContents(), true);
            // Log::info('PUT webhook replace successful', $body);

            return $body['data'] ?? [];
        } catch (RequestException $e) {
            $body = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : 'No response body';
            Log::error('Alchemy Webhook REPLACE update failed: ' . $e->getMessage() . ' - Response: ' . $body);
            throw $e;
        }
    }

    public function deleteWebhook(string $webhookId): array
    {
        try {
            $response = $this->client->delete('api/delete-webhook', [
                'query' => ['webhook_id' => $webhookId],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            // Log::info('Webhook deleted:', $data);

            return $data;
        } catch (RequestException $e) {
            $body = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : 'No response body';
            Log::error('Alchemy Webhook delete failed: ' . $e->getMessage() . ' - Response: ' . $body);
            throw $e;
        }
    }

    public function listWebhooks(): array
    {
        try {
            $response = $this->client->get('api/team-webhooks'); // <--- Fixed

            $data = json_decode($response->getBody()->getContents(), true);
            // Log::info('Team webhooks fetched successfully:', $data);

            return $data['data'] ?? [];
        } catch (RequestException $e) {
            $body = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : 'No response body';
            Log::error('Fetching team webhooks failed: ' . $e->getMessage() . ' - Response: ' . $body);
            throw $e;
        }
    }

    public function getWebhookAddresses(array $params): array
    {
        try {
            $response = $this->client->get('api/webhook-addresses', [
                'query' => $params,
            ]);

            $body = json_decode($response->getBody()->getContents(), true);

            // Optional: Validate expected structure
            if (!is_array($body) || !isset($body['data'])) {
                throw new \RuntimeException('Invalid response structure from Alchemy API');
            }

            return $body;
        } catch (RequestException $e) {
            $body = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : 'No response';
            Log::error("Webhook address fetch failed: {$e->getMessage()} - {$body}");
            throw $e;
        } catch (\Throwable $e) {
            Log::error("Unexpected error fetching webhook addresses: " . $e->getMessage());
            throw $e;
        }
    }

    // Get All Created Variables inside Addresses and Check Before Updations Variables
    public function getVariableAddresses(string $variableName): array
    {
        $allAddresses = [];
        $afterCursor = null;

        try {
            do {
                $queryParams = $afterCursor ? ['after' => $afterCursor] : [];

                $response = $this->client->get("api/graphql/variables/{$variableName}", [
                    'query' => $queryParams,
                    'timeout' => 120,
                ]);

                $data = json_decode($response->getBody()->getContents(), true);

                // Merge current page addresses
                if (!empty($data['data'])) {
                    $allAddresses = array_merge($allAddresses, $data['data']);
                }

                // Check if there is a next page
                $afterCursor = $data['pagination']['cursors']['after'] ?? null;

            } while ($afterCursor);

            return $allAddresses;
        }
        catch (RequestException $e) {
            $body = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : 'No response body';
            Log::error("Alchemy getVariableAddresses failed: {$e->getMessage()} - {$body}");
            throw $e;
        }
    }

    //---Single page filtered by address---//
    // public function getVariableAddressesPaginated(string $variableName, int $limit = 50, ?string $after = null): array
    // {
    //     try {
    //         $queryParams = ['limit' => $limit];
    //         if ($after) $queryParams['after'] = $after;

    //         $response = $this->client->get("api/graphql/variables/{$variableName}", [
    //             'query' => $queryParams,
    //             'timeout' => 120,
    //         ]);

    //         $data = json_decode($response->getBody()->getContents(), true);

    //         return [
    //             'addresses' => $data['data'] ?? [],
    //             'pagination' => $data['pagination']['cursors'] ?? [],
    //         ];
    //     }
    //     catch (RequestException $e) {
    //         $body = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : 'No response body';
    //         Log::error("Alchemy getVariableAddressesPaginated failed: {$e->getMessage()} - {$body}");
    //         throw $e;
    //     }
    // }

    /**
     * Fetch paginated list of variable addresses.
     */
    public function getListedVariables(string $variableName = 'userWallets', string $after = null, int $limit = 100): array
    {
        try {
            $queryParams = ['limit' => $limit];
            if ($after) {
                $queryParams['after'] = $after;
            }

            $response = $this->client->get("api/graphql/variables/{$variableName}", [
                'query' => $queryParams
            ]);

            return json_decode($response->getBody()->getContents(), true);
        }
        catch (RequestException $e) {
            $body = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : 'No response body';
            Log::error("Fetching variables [{$variableName}] failed: {$e->getMessage()} - Response: {$body}");
            throw $e;
        }
    }

    /**
     * Search for an address across all pages (scans until match found).
     */
    public function searchVariableAddress(string $variableName, string $needle, int $batchSize = 500): array
    {
        $matches = [];
        $after = null;

        do {
            $queryParams = ['limit' => $batchSize];
            if ($after) {
                $queryParams['after'] = $after;
            }

            $response = $this->client->get("api/graphql/variables/{$variableName}", [
                'query' => $queryParams
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            $addresses = $data['data'] ?? [];
            $pagination = $data['pagination'] ?? [];

            foreach ($addresses as $addr) {
                if (stripos($addr, $needle) !== false) {
                    $matches[] = $addr;
                }
            }

            $after = $pagination['cursors']['after'] ?? null;

            // stop early if matches found (remove if you want all matches across all pages)
            if (!empty($matches)) {
                break;
            }

        } while ($after);

        return $matches;
    }

    public function createVariable(string $variableName, array $addresses): array
    {
        $url = "api/graphql/variables/{$variableName}";
        // Log::info("Creating new variable {$variableName} with chunk of " . count($addresses) . " addresses");

        try {
            $response = $this->client->post($url, [
                'json' => ['items' => $addresses],
                'timeout' => 120, // longer timeout for large payloads
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            // Log::info("Variable {$variableName} created successfully", ['response' => $data]);
            return $data;
        }
        catch (RequestException $e) {
            $body = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : 'No response body';

            Log::error("Alchemy createVariable failed: {$e->getMessage()}", ['response' => $body]);
            throw $e;
        }
    }

    public function patchVariable(string $variableName, array $add = [], array $delete = []): array
    {
        $url = "api/graphql/variables/{$variableName}";

        $payload = [];

        if (!empty($add)) {
            $payload['add'] = $add;
        }

        if (!empty($delete)) {
            $payload['delete'] = $delete;
        }

        // If both are empty, throw exception
        if (empty($payload)) {
            throw new \Exception("Nothing to add or delete for variable '{$variableName}'");
        }

        try {
            $response = $this->client->patch($url, [
                'json'    => $payload,
                'timeout' => 120,
            ]);

            return json_decode($response->getBody()->getContents(), true);
        }
        catch (RequestException $e) {
            $body = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : 'No response body';

            Log::error("Alchemy patchVariable failed: {$e->getMessage()} - {$body}");
            throw $e;
        }
    }

    // DELETE entire variable
    public function deleteVariable(string $variableName): array
    {
        $url = "api/graphql/variables/{$variableName}";

        try {
            $response = $this->client->delete($url, [
                'timeout' => 60,
            ]);

            return json_decode($response->getBody()->getContents(), true);
        }
        catch (RequestException $e) {
            $body = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : 'No response body';
            
            Log::error("Alchemy deleteVariable failed: {$e->getMessage()} - {$body}");
            throw $e;
        }
    }

    // NEW method for variable-based webhook
    public function createVariableWebhook(string $network, string $variableName, string $webhookUrl): array
    {        
        // GraphQL query for callTracerTraces using Alchemy variable
        // $query = <<<GRAPHQL
        // {
        //     block {
        //         hash
        //         number
        //         callTracerTraces(
        //             filter: {
        //                 addresses: [
        //                     {from: [in: [\$alchemy_variable({$variableName})]], to: []}
        //                 ]
        //             }
        //         ) {
        //             from { address }
        //             to { address }
        //             type
        //             input
        //             output
        //             value
        //             gas
        //             gasUsed
        //             error
        //             revertReason
        //             subtraceCount
        //             traceAddressPath
        //         }
        //     }
        // }
        // GRAPHQL;

        // GraphQL query with a variable
        // $query = <<<GRAPHQL
        // query (\${$variableName}: [Address!]) {
        //   block {
        //     logs(filter: {addresses: \${$variableName}}) {
        //       transaction {
        //         hash
        //         from { address }
        //         to { address }
        //         value
        //       }
        //     }
        //   }
        // }
        // GRAPHQL;

        $query = <<<GRAPHQL
            query (\${$variableName}: [Address!]) {
                block {
                    transactions(
                        filter: {
                            addresses: [
                                { from: \${$variableName} }
                                { to: \${$variableName} }
                            ]
                        }
                    ) {
                        hash
                        from { address }
                        to { address }
                        value
                        gas
                        status
                    }
                }
            }
        GRAPHQL;

        $payload = [
            'network' => $network,
            'webhook_type' => 'GRAPHQL',
            'webhook_url' => $webhookUrl,
            'graphql_query' => [
                'query' => $query,
                'skip_empty_messages' => false,
            ],
            'app_id' => config('services.alchemy.app_id'),
        ];

        Log::info("Creating GraphQL webhook with payload", $payload);

        try {
            $response = $this->client->post('api/create-webhook', [
                'headers' => [
                    'X-Alchemy-Token' => config('services.alchemy.token'),
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
            ]);

            $body = $response->getBody()->getContents();
            $data = json_decode($body, true) ?? [];

            Log::info("GraphQL webhook creation response", $data);
            return $data;
        }
        catch (RequestException $e) {
            $body = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : 'No response body';
            Log::error("GraphQL webhook creation failed: {$e->getMessage()} - {$body}");
            throw $e;
        }
    }
}
