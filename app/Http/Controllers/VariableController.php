<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Models\CustomVariable;
use App\Services\AlchemyWebhookService;
use Illuminate\Support\Facades\Crypt;

class VariableController extends Controller
{
    protected AlchemyWebhookService $alchemy;

    public function __construct(AlchemyWebhookService $alchemy)
    {
        $this->alchemy = $alchemy;
    }

    public function getAllVariables(Request $request)
    {
        $search = strtolower($request->input('search', ''));

        $variables = CustomVariable::latest();

        if ($search) {
            $variables = $variables->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);
        }

        $variables = $variables->get();

        return view('admin.variables.index', compact('variables', 'search'));
    }

    //---Single page filtered by address---//
    // public function viewVariableAddresses(Request $request, $encryptedId)
    // {
    //     $id = Crypt::decrypt($encryptedId);
    //     $variable = CustomVariable::findOrFail($id);

    //     $limit = 50; // addresses per page
    //     $after = $request->input('after'); // cursor
    //     $search = strtolower($request->input('search', ''));

    //     // Fetch paginated addresses
    //     $response = $this->alchemy->getVariableAddressesPaginated($variable->name, $limit, $after);

    //     $addresses = collect($response['addresses']);

    //     // Server-side filtering
    //     if ($search) {
    //         $addresses = $addresses->filter(fn($addr) => str_contains(strtolower($addr), $search));
    //     }

    //     $pagination = $response['pagination'];

    //     return view('admin.variables.view', compact('variable', 'addresses', 'pagination', 'limit', 'after', 'search'));
    // }

    //---Full Dataset filtered by address---//
    public function viewVariableAddresses(Request $request, $encryptedId, AlchemyWebhookService $svc)
    {
        try {
            $id = Crypt::decrypt($encryptedId);
            $variable = CustomVariable::findOrFail($id);

            $variableName = $variable->name;
            $after = $request->input('after');
            $search = strtolower($request->input('search', ''));

            $addresses = collect();
            $pagination = [];

            if ($search) {
                $addresses = collect($svc->searchVariableAddress($variableName, $search));
            } else {
                $result = $svc->getListedVariables($variableName, $after);
                $addresses = collect($result['data'] ?? []);
                $pagination = $result['pagination']['cursors'] ?? [];
            }

            return view('admin.variables.view', compact(
                'variable', 'addresses', 'pagination', 'search', 'encryptedId'
            ));
        }
        catch (\Exception $e) {
            return redirect()->route('variables.index')
                ->with('error', 'Failed to fetch addresses: '.$e->getMessage());
        }
    }


    public function syncVariable(Request $request, $encryptedId)
    {
        $id = Crypt::decrypt($encryptedId);
        $variable = CustomVariable::findOrFail($id);
        $addresses = $this->alchemy->getVariableAddresses($variable->name);
        $variable->total_addresses = count($addresses);
        $variable->save();

        return back()->with('success', "Variable '{$variable->name}' synced successfully.");
    }

    public function manage()
    {
        $variables = CustomVariable::latest()->get();
        return view('admin.variables.manage', compact('variables'));
    }

    // CREATE (Upload new CSV + Create variable)
    public function createVariablesViaCsv(Request $request)
    {
        $request->validate([
            'variable_name' => 'required|string',
            'csv_file' => 'required|file|mimes:csv,txt|max:51200', // 50MB max
        ]);

        // Extract and clean addresses
        $addresses = $this->extractAddresses($request->file('csv_file')->getRealPath());

        if (empty($addresses)) {
            return back()->withErrors(['csv_file' => 'No valid wallet addresses found in file.']);
        }

        $chunks = array_chunk($addresses, 10000); // Chunking for large files
        $variableName = $request->variable_name;

        DB::beginTransaction();
        try {
            // Check if variable already exists in DB
            if (CustomVariable::where('name', $variableName)->exists()) {
                return back()->withErrors([
                    'variable_name' => "Custom variable '{$variableName}' already exists in DB."
                ]);
            }

            // Create variable in Alchemy API in chunks (if needed)
            foreach ($chunks as $i => $chunk) {
                if ($i === 0) {
                    // First chunk creates the variable
                    $this->alchemy->createVariable($variableName, $chunk);
                }
                else {
                    // For Alchemy API without add, we could skip or store as separate variable
                    // Here we log warning for skipped chunks
                    Log::warning("Skipped chunk #".($i+1)." for variable '{$variableName}' because Alchemy API does not support 'add'");
                }
            }

            // Save in DB manually
            $customVariable = new CustomVariable();
            $customVariable->name = $variableName;
            $customVariable->total_addresses = count($addresses);
            $customVariable->save();

            DB::commit();
            return back()->with('success', 'Variable created & synced successfully.');
        }
        catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Upload failed", ['error' => $e->getMessage()]);
            return back()->withErrors(['csv_file' => 'Upload failed. Check logs for details.']);
        }
    }

    // UPDATE (Add new addresses to an existing variable)
    public function updateVariable(Request $request)
    {
        $request->validate([
            'variable_name'    => 'required|string|exists:custom_variables,name',
            'add_addresses'    => 'nullable|string',
            'delete_addresses' => 'nullable|string',
        ]);

        $variableName = $request->variable_name;

        // Convert comma-separated input into array and trim each entry
        $parseAddresses = function ($input) {
            return collect(explode(',', $input))
                ->map(fn($a) => trim($a))
                ->filter(fn($a) => !empty($a))
                ->unique()
                ->values()
                ->toArray();
        };

        $add    = $request->filled('add_addresses') ? $parseAddresses($request->add_addresses) : [];
        $delete = $request->filled('delete_addresses') ? $parseAddresses($request->delete_addresses) : [];

        try {
            // Fetch existing addresses from Alchemy
            $existingAddresses = $this->alchemy->getVariableAddresses($variableName);

            // Normalize existing addresses
            $existingAddresses = array_map(fn($a) => strtolower(trim($a)), $existingAddresses);

            // Normalize new addresses
            $add = array_map(fn($a) => strtolower(trim($a)), $add);

            // Check for duplicates in "add"
            $duplicates = array_intersect($add, $existingAddresses);
            if (!empty($duplicates)) {
                return back()->withErrors([
                    'add_addresses' => 'These addresses already exist: ' . implode(', ', $duplicates)
                ]);
            }

            // Normalize new addresses
            $delete = array_map(fn($a) => strtolower(trim($a)), $delete);

            // Check for invalid addresses in "delete"
            $invalidDeletes = array_diff($delete, $existingAddresses);
            if (!empty($invalidDeletes)) {
                return back()->withErrors([
                    'delete_addresses' => 'These addresses do not exist in the variable: ' . implode(', ', $invalidDeletes)
                ]);
            }

            // Call patchVariable (with corrected payload for empty arrays)
            $this->alchemy->patchVariable($variableName, $add, $delete);

            // update counts locally
            $variable = CustomVariable::where('name', $variableName)->first();
            if ($variable) {
                $variable->total_addresses += count($add);
                $variable->total_addresses -= count($delete);
                if ($variable->total_addresses < 0) {
                    $variable->total_addresses = 0;
                }
                $variable->save();
            }

            return back()->with('success', 'Variable updated successfully.');
        }
        catch (\Throwable $e) {
            Log::error("Variable update failed", ['error' => $e->getMessage()]);
            return back()->withErrors(['add_addresses' => 'Update failed. Check logs.']);
        }
    }

    // DELETE variable (entire variable, not just addresses)
    public function deleteVariable(Request $request)
    {
        $request->validate([
            'variable_name' => 'required|string|exists:custom_variables,name',
        ]);

        $variableName = $request->variable_name;

        try {
            // Call Alchemy delete API
            $this->alchemy->deleteVariable($variableName);

            // Delete from DB
            $variable = CustomVariable::where('name', $variableName)->first();
            if ($variable) {
                $variable->delete();
            }

            return back()->with('success', "Variable '{$variableName}' deleted successfully.");
        } catch (\Throwable $e) {
            Log::error("Delete variable failed", ['error' => $e->getMessage()]);
            return back()->withErrors(['variable_name' => 'Delete failed. Check logs.']);
        }
    }

    /**
     * Extract addresses from CSV file
     * Trims spaces and validates Ethereum addresses
     */
    private function extractAddresses(string $filePath): array
    {
        $rows = array_map('str_getcsv', file($filePath));
        $addresses = [];

        foreach ($rows as $row) {
            foreach ($row as $value) {
                $value = trim($value);
                if ($value !== '' && preg_match('/^0x[a-fA-F0-9]{40}$/', $value)) {
                    $addresses[] = $value;
                } else {
                    Log::warning("Invalid address skipped", ['value' => $value]);
                }
            }
        }

        return $addresses;
    }
}
