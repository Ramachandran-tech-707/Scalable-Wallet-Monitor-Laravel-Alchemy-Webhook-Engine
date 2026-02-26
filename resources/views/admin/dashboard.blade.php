@extends('layouts.app')

@section('content')
<div class="container incrase-width py-4"> 

    {{-- Dashboard Header --}}
    <div class="mb-4">
        <h2 class="fw-bold text-primary"><i class="bi bi-speedometer2 me-2"></i>Alchemy Webhooks Dashboard</h2>
        <p class="text-muted">Quick overview of webhooks, wallet histories, and custom variables.</p>
    </div>

    {{-- Stats Cards --}}
    <div class="row g-4">
        {{-- Total Webhooks --}}
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="me-3 text-primary fs-1">
                        <i class="bi bi-diagram-3"></i>
                    </div>
                    <div>
                        <h6 class="text-muted">Total Webhooks</h6>
                        <h3 class="mb-0">{{ $webhooksCount ?? 0 }}</h3>
                        <a href="{{ route('webhooks.history') }}" class="small text-decoration-none">View History →</a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Wallet Histories --}}
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="me-3 text-success fs-1">
                        <i class="bi bi-wallet2"></i>
                    </div>
                    <div>
                        <h6 class="text-muted">Wallet Histories</h6>
                        <h3 class="mb-0">{{ $walletsCount ?? 0 }}</h3>
                        <a href="{{ route('wallets.index') }}" class="small text-decoration-none">Manage Wallets →</a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Custom Variables --}}
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="me-3 text-warning fs-1">
                        <i class="bi bi-database-fill"></i>
                    </div>
                    <div>
                        <h6 class="text-muted">Custom Variables</h6>
                        <h3 class="mb-0">{{ $variablesCount ?? 0 }}</h3>
                        <a href="{{ route('variables.index') }}" class="small text-decoration-none">View Variables →</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Activity --}}
    <div class="row g-4 mt-4">
        {{-- Recent Webhook Events --}}
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white d-flex align-items-center">
                    <i class="bi bi-clock-history me-2"></i> Recent Webhook Events
                </div>
                <div class="card-body p-0" style="max-height: 250px; overflow-y:auto;">
                    <ul class="list-group list-group-flush">
                        @forelse($recentWebhooks as $log)
                            <li class="list-group-item">
                                <small class="text-muted">{{ $log['createdAt'] ? $log['createdAt']->format('d M Y H:i') : 'N/A' }}</small><br>

                                <strong>{{ $log['event_type'] }}</strong> - 
                                @php
                                    $status = $log['status'] ?? 'Unknown';
                                    $badgeClass = match($status) {
                                        'Active' => 'bg-success',
                                        'Inactive' => 'bg-danger',
                                        default => 'bg-secondary'
                                    };
                                @endphp
                                <span class="badge {{ $badgeClass }}">{{ $status }}</span>

                                <!-- @if(!empty($log['deactivation']))
                                    <br><small class="text-danger">Reason: {{ $log['deactivation'] }}</small>
                                @endif -->

                                <br><small class="text-truncate d-block" style="max-width:300px;">URL: {{ $log['webhook_url'] }}</small>
                            </li>
                        @empty
                            <li class="list-group-item text-muted">No recent webhooks</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        {{-- Recent Custom Variables --}}
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-warning text-dark d-flex align-items-center">
                    <i class="bi bi-database me-2"></i> Recent Custom Variables
                </div>
                <div class="card-body p-0" style="max-height: 250px; overflow-y:auto;">
                    <ul class="list-group list-group-flush">
                        @forelse($recentVariables ?? [] as $var)
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $var['name'] ?? $var->name }}</strong>
                                        <small class="text-muted">
                                            ({{ isset($var['created_at']) ? \Carbon\Carbon::parse($var['created_at'])->diffForHumans() : $var->created_at->diffForHumans() ?? 'N/A' }})
                                        </small>
                                    </div>
                                    <a href="{{ route('variables.view', Crypt::encrypt($var['id'] ?? $var->id)) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </div>
                                @if(!empty($var['addresses']))
                                    <div class="mt-1">
                                        <span class="badge bg-light text-dark text-truncate d-block" style="max-width: 100%;">
                                            {{ implode(', ', $var['addresses']) }}
                                        </span>
                                    </div>
                                @endif
                            </li>
                        @empty
                            <li class="list-group-item text-muted text-center">No variables created yet</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
