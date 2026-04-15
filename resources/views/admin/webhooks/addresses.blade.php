@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h4>Webhook Addresses: <code>{{ $webhookId }}</code></h4>

    <a href="{{ route('webhooks.history') }}" class="btn btn-secondary mb-3">
        ← Back to Webhook History
    </a>

    <form method="GET" action="{{ route('webhooks.addresses', ['webhookId' => $webhookId]) }}" class="row g-3 mb-4">
        <input type="hidden" name="webhook_id" value="{{ $webhookId }}">

        <div class="col-md-3">
            <label class="form-label">Limit</label>
            <input type="number" name="limit" class="form-control" value="{{ $filters['limit'] ?? 100 }}">
        </div>

        <div class="col-md-3">
            <label class="form-label">After</label>
            <input type="text" name="after" class="form-control" value="{{ $filters['after'] }}">
        </div>

        <div class="col-md-3">
            <label class="form-label">Page Key</label>
            <input type="text" name="pageKey" class="form-control" value="{{ $filters['pageKey'] }}">
        </div>

        <div class="col-md-3 d-flex align-items-end">
            <button class="btn btn-primary me-2" type="submit">Filter</button>
            <a href="{{ route('webhooks.addresses', ['webhookId' => $webhookId]) }}" class="btn btn-outline-secondary">Reset</a>
        </div>
    </form>

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Wallet Address</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($addresses as $index => $address)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td><code>{{ $address }}</code></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="text-center text-muted">No records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Total Count --}}
    <div class="mt-3">
        <strong>Total Count:</strong> {{ $totalCount }}
    </div>

    {{-- Load More button --}}
    @if (!empty($pagination['after']))
        <div class="mt-4">
            <form method="GET" action="{{ route('webhooks.addresses', ['webhookId' => $webhookId]) }}">
                <input type="hidden" name="limit" value="{{ $filters['limit'] ?? 100 }}">
                <input type="hidden" name="after" value="{{ $pagination['after'] }}">
                <input type="hidden" name="pageKey" value="{{ $filters['pageKey'] }}">
                <button type="submit" class="btn btn-success">
                    Load More →
                </button>
            </form>
        </div>
    @endif
</div>
@endsection
