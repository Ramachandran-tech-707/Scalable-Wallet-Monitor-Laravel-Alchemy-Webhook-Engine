@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2><i class="bi bi-wallet2 me-2"></i>User Wallets</h2>
        </div>

        <form method="GET" action="{{ route('wallets.index') }}" class="mb-4">
            <div class="row g-2">
                <div class="col-md-6">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                        placeholder="Search by Address or User ID">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> Search
                    </button>
                    <a href="{{ route('wallets.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Reset
                    </a>
                </div>
            </div>
        </form>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>User ID</th>
                                <th>Address</th>
                                <th>Balance</th>
                                <th>Last Tx</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($wallets as $wallet)
                                <tr>
                                    <td>{{ $wallet->user_id }}</td>
                                    <td class="text-break">{{ $wallet->address }}</td>
                                    <td><span class="badge bg-success">{{ number_format($wallet->balance, 6) }}</span></td>
                                    <td>{{ $wallet->last_tx_hash ? Str::limit($wallet->last_tx_hash, 20) : '---' }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('wallets.show', $wallet->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <i class="bi bi-exclamation-circle me-2"></i>No wallets found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($wallets->hasPages())
                <div class="card-footer">
                    <div class="d-flex justify-content-center">
                        {{ $wallets->appends(request()->query())->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
