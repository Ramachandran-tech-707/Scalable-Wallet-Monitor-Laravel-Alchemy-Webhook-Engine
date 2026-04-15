@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h2 class="mb-4"><i class="bi bi-gear-fill me-2"></i>Manage Alchemy Webhooks</h2>

    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="alert alert-success d-flex align-items-center alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger d-flex align-items-center alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger">
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>

        <strong>There were some issues:</strong>
        <ul class="mb-0 mt-1">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Create Bulk Webhook --}}
    <div class="card mb-5 shadow-sm border-0 rounded-4">
        <div class="card-header bg-success text-white fw-semibold">
            <i class="bi bi-upload me-2"></i>Create Webhook (Manual OR CSV Upload)
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.webhook.create') }}" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label for="network" class="form-label">Network</label>
                    <select name="network" class="form-select" required>
                        <option value="ETH_MAINNET" {{ old('network') == 'ETH_MAINNET' ? 'selected' : '' }}>Ethereum Mainnet</option>
                        <option value="ETH_SEPOLIA" {{ old('network') == 'ETH_SEPOLIA' ? 'selected' : '' }}>Sepolia Testnet</option>
                    </select>
                </div>

                {{-- Manual Entry --}}
                <div class="border rounded-3 p-3 mb-3 bg-light">
                    <label for="addresses" class="form-label fw-semibold">Manual Ethereum Addresses</label>
                    <textarea name="addresses" class="form-control" id="addresses" placeholder="0xabc..., 0xdef..." style="height: 100px">{{ old('addresses') }}</textarea>
                    <div class="form-text text-muted">Enter comma-separated Ethereum addresses.</div>
                </div>

                <div class="text-center mb-3 fw-semibold text-secondary">
                    — OR —
                </div>

                {{-- CSV Upload --}}
                <div class="border rounded-3 p-3 mb-3 bg-light">
                    <label for="csv_file" class="form-label fw-semibold">CSV File Upload</label>
                    <input type="file" name="csv_file" class="form-control" accept=".csv">
                    <div class="form-text text-muted">CSV should contain one address per line.</div>
                </div>

                {{-- Warning Message --}}
                <div class="form-text text-danger mb-3">
                    * Please fill either manual addresses OR upload a CSV — not both.
                </div>

                <div class="mb-3">
                    <label for="webhook_url" class="form-label">Webhook URL</label>
                    <input type="url" name="webhook_url" class="form-control" id="webhook_url"
                        placeholder="https://yourdomain.com/api/webhooks/alchemy" required>
                </div>

                <button type="submit" class="btn btn-success">
                    <i class="bi bi-plus-circle me-1"></i>Create Webhook
                </button>
            </form>
        </div>
    </div>

    {{-- PATCH: Add/Remove Addresses --}}
    <div class="card mb-5 shadow-sm border-0 rounded-4">
        <div class="card-header bg-primary text-white fw-semibold">
            <i class="bi bi-pencil-square me-2"></i>Update Webhook (Add/Remove Addresses)
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.webhook.patch') }}">
                @csrf
                <div class="form-floating mb-3">
                    <input type="text" name="webhook_id" class="form-control" id="patch_webhook_id" placeholder="Webhook ID" required>
                    <label for="patch_webhook_id">Webhook ID</label>
                </div>

                <div class="mb-3">
                    <label for="addresses_to_add" class="form-label">Addresses to Add (comma-separated)</label>
                    <textarea name="addresses_to_add" class="form-control" id="addresses_to_add" placeholder="0xabc..., 0xdef..." style="height: 100px">{{ old('addresses_to_add') }}</textarea>
                </div>

                <div class="mb-3">
                    <label for="addresses_to_remove" class="form-label">Addresses to Remove (comma-separated)</label>
                    <textarea name="addresses_to_remove" class="form-control" id="addresses_to_remove" placeholder="0x123..., 0x456..." style="height: 100px">{{ old('addresses_to_remove') }}</textarea>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-pencil me-1"></i>Patch Webhook
                </button>
            </form>
        </div>
    </div>

    {{-- PUT: Replace All Addresses --}}
    <div class="card mb-5 shadow-sm border-0 rounded-4">
        <div class="card-header bg-warning text-dark fw-semibold">
            <i class="bi bi-arrow-repeat me-2"></i>Update Webhook (Replace All Addresses)
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.webhook.replace') }}">
            @csrf
                <div class="form-floating mb-3">
                    <input type="text" name="webhook_id" class="form-control" id="replace_webhook_id" placeholder="Webhook ID" required>
                    <label for="replace_webhook_id">Webhook ID</label>
                </div>

                <div class="mb-3">
                    <label for="replace_addresses" class="form-label">New Addresses (comma-separated)</label>
                    <textarea name="addresses" class="form-control" id="replace_addresses" placeholder="0xa1b2..., 0xc3d4..." style="height: 100px">{{ old('addresses') }}</textarea>
                </div>

                <button type="submit" class="btn btn-warning">
                    <i class="bi bi-arrow-repeat me-1"></i>Replace Addresses
                </button>
            </form>
        </div>
    </div>

    {{-- Delete Webhook --}}
    <div class="card mb-5 shadow-sm border-0 rounded-4">
        <div class="card-header bg-danger text-white fw-semibold">
            <i class="bi bi-trash3-fill me-2"></i>Delete Webhook
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.webhook.delete') }}">
                @csrf
                <div class="form-floating mb-3">
                    <input type="text" name="webhook_id" class="form-control" id="delete_webhook_id" placeholder="Enter Webhook ID to Delete" required value="{{ old('webhook_id') }}">
                    <label for="delete_webhook_id">Webhook ID</label>
                </div>

                <button type="submit" class="btn btn-danger">
                    <i class="bi bi-trash3 me-1"></i>Delete Webhook
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        const addressesInput = document.querySelector('[name="addresses"]');
        const csvInput = document.querySelector('[name="csv_file"]');

        addressesInput?.addEventListener('input', () => {
            if (addressesInput.value.trim()) {
                csvInput.disabled = true;
            } else {
                csvInput.disabled = false;
            }
        });

        csvInput?.addEventListener('change', () => {
            if (csvInput.files.length > 0) {
                addressesInput.disabled = true;
            } else {
                addressesInput.disabled = false;
            }
        });
    </script>
@endpush