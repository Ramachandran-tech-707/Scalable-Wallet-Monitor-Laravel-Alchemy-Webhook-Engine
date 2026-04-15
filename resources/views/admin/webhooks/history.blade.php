@extends('layouts.app')

@section('content')
<div class="container incrase-width py-4"> 
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Alchemy Webhook History</h2>
    </div>

    <form method="GET" action="{{ route('webhooks.history') }}" class="row g-2 mb-4">
        <div class="col-md-6">
            <input type="text" name="search" class="form-control" placeholder="Search by name, network, or ID..." value="{{ request('search') }}">
        </div>
        <!-- <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">All Status</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div> -->

        <div class="col-md-auto">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-search"></i> Search
            </button>
            <a href="{{ route('webhooks.history') }}" class="btn btn-outline-secondary ms-2">
                <i class="bi bi-x-circle"></i> Reset
            </a>
        </div>
    </form>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if(empty($webhooks))
        <div class="alert alert-warning">No webhook records found.</div>
    @else
        <div class="table-responsive">
            <table class="table table-bordered align-middle w-100">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Network</th>
                        <th>Webhook Type</th>
                        <th>Webhook URL</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($webhooks as $webhook)
                        <tr>
                            <td>{{ $webhook['id'] }}</td>
                            <td>{{ $webhook['name'] ?? '---' }}</td>
                            <td>{{ $webhook['network'] ?? '---' }}</td>
                            <td>{{ $webhook['webhook_type'] ?? '---' }}</td>
                            <td>{{ $webhook['webhook_url'] ?? '---' }}</td>
                            <td>
                                <span class="badge bg-{{ $webhook['is_active'] ? 'success' : 'danger' }}">
                                    {{ $webhook['is_active'] ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                {{ \Carbon\Carbon::createFromTimestampMs($webhook['time_created'])->toDayDateTimeString() }}
                            </td>

                            <td>
                                <a href="{{ route('webhooks.addresses', ['webhookId' => $webhook['id']]) }}"
                                class="btn btn-sm btn-primary">
                                    View Addresses
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
