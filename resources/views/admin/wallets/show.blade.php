@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="bi bi-clock-history me-2"></i>Transactions for Wallet
        </h2>
        <a href="{{ route('wallets.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-light">
            <strong>Wallet Address:</strong> <span class="text-monospace">{{ $wallet->address }}</span>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered mb-0 table-sm align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Tx Hash</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Block</th>
                            <th>Asset</th>
                            <th>Value</th>
                            <th>Category</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transactions as $tx)
                            <tr>
                                <td class="text-break">{{ Str::limit($tx->tx_hash, 20) }}</td>
                                <td class="text-break">{{ Str::limit($tx->from_address, 15) }}</td>
                                <td class="text-break">{{ Str::limit($tx->to_address, 15) }}</td>
                                <td>{{ $tx->block_number }}</td>
                                <td><span class="badge bg-info text-dark">{{ $tx->asset }}</span></td>
                                <td>{{ rtrim(rtrim(number_format($tx->value, 6, '.', ''), '0'), '.') }}</td>
                                <td><span class="text-capitalize">{{ $tx->category }}</span></td>
                                <td>{{ $tx->created_at->diffForHumans() }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="bi bi-exclamation-triangle me-2"></i>No transactions found for this wallet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($transactions->hasPages())
            <div class="card-footer">
                <div class="d-flex justify-content-center">
                    {{ $transactions->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
