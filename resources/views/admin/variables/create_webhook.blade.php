@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="card shadow-lg border-0">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Create Webhook For Custom-Variable</h4>
        </div>
        <div class="card-body">
            
            {{-- Success / Error Messages --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form action="{{ route('admin.webhook.createVariable') }}" method="POST">
                @csrf

                {{-- Network --}}
                <div class="mb-3">
                    <label for="network" class="form-label">Network</label>
                    <select name="network" id="network" class="form-select" required>
                        <option value="">-- Select Network --</option>
                        <option value="ETH_MAINNET">Ethereum Mainnet</option>
                        <option value="ETH_SEPOLIA">Ethereum Sepolia</option>
                    </select>
                    @error('network')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Webhook URL --}}
                <div class="mb-3">
                    <label for="webhook_url" class="form-label">Webhook URL</label>
                    <input type="url" name="webhook_url" id="webhook_url" 
                           class="form-control" placeholder="https://yourdomain.com/alchemy/webhook"
                           required>
                    @error('webhook_url')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Variable Name (Manual Input) --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">Select Variable</label>
                    <select name="variable_name" class="form-select" required>
                        <option value="" disabled selected>Select Variable</option>
                        @foreach($variables as $variable)
                            <option value="{{ $variable->name }}">
                                {{ $variable->name }} ({{ $variable->total_addresses }} addresses)
                            </option>
                        @endforeach
                    </select>
                    @error('variable_name')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="d-flex justify-content-end">
                    <button type="reset" class="btn btn-outline-secondary me-2">Clear</button>
                    <button type="submit" class="btn btn-primary">Create Webhook</button>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection
