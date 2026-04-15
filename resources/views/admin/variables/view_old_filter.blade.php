@extends('layouts.app')

@section('content')
<div class="container mt-4">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <i class="bi bi-wallet2 me-2"></i>{{ $variable->name }} - Addresses ({{ $variable->total_addresses }})
        </h4>
        <a href="{{ route('variables.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back to Variables
        </a>
    </div>

    {{-- Filter Card --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-body d-flex flex-column flex-md-row gap-2 align-items-md-center">
            <form method="GET" action="{{ route('variables.view', Crypt::encrypt($variable->id)) }}" class="d-flex gap-2 flex-grow-1">
                <input type="text" name="search" class="form-control" placeholder="Search addresses..." value="{{ $search ?? '' }}">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i> Search
                </button>
            </form>
            <form method="GET" action="{{ route('variables.view', Crypt::encrypt($variable->id)) }}">
                <button type="submit" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-clockwise"></i> Refresh
                </button>
            </form>
        </div>
    </div>

    {{-- Address List Card --}}
    <div class="card shadow-sm">
        <div class="card-body">
            @if($addresses->count())
                <ul class="list-group list-group-flush">
                    @foreach($addresses as $addr)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="text-monospace">{{ $addr }}</span>
                        </li>
                    @endforeach
                </ul>

                {{-- Pagination Controls --}}
                <div class="mt-3 d-flex justify-content-between align-items-center">
                    @if(!empty($pagination['after']))
                        <a href="{{ route('variables.view', Crypt::encrypt($variable->id)) . '?after=' . $pagination['after'] . '&search=' . ($search ?? '') }}"
                           class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-chevron-right"></i> Next
                        </a>
                    @else
                        <span></span>
                    @endif
                    <small class="text-muted">Showing {{ $addresses->count() }} addresses</small>
                </div>
            @else
                <div class="text-center text-muted py-4">
                    <i class="bi bi-exclamation-circle me-2"></i>No addresses found for this variable.
                </div>
            @endif
        </div>
    </div>

</div>
@endsection
