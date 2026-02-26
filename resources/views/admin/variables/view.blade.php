@extends('layouts.app')

@section('content')
<div class="container mt-4">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <i class="bi bi-database-fill me-2"></i>{{ $variable->name }} - Addresses ({{ $variable->total_addresses }})
        </h4>
        <a href="{{ route('variables.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back to Variables
        </a>
    </div>

    {{-- Search / Refresh --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-body d-flex flex-column flex-md-row gap-2 align-items-md-center">

            {{-- Search --}}
            <form method="GET" 
                  action="{{ route('variables.view', ['encryptedId' => Crypt::encrypt($variable->id)]) }}" 
                  class="d-flex gap-2 flex-grow-1">
                <input type="text" 
                       name="search" 
                       class="form-control" 
                       placeholder="Search addresses..." 
                       value="{{ $search ?? '' }}">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i> Search
                </button>
            </form>

            {{-- Refresh --}}
            <form method="GET" 
                  action="{{ route('variables.view', ['encryptedId' => Crypt::encrypt($variable->id)]) }}">
                <button type="submit" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-clockwise"></i> Refresh
                </button>
            </form>
        </div>
    </div>

    {{-- Addresses Table --}}
    <div class="card shadow-sm">
        <div class="card-body p-0">
            @if(count($addresses))
                <table class="table table-bordered table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 80px">#</th>
                            <th>Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($addresses as $index => $addr)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $addr }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- Pagination --}}
                @if(!empty($pagination['after']) && !$search)
                    <div class="mt-3 d-flex justify-content-between align-items-center px-3">
                        <span>Showing {{ count($addresses) }} addresses</span>
                        <a href="{{ route('variables.view', [
                            'encryptedId' => Crypt::encrypt($variable->id),
                            'search' => $search,
                            'after' => $pagination['after']
                        ]) }}" class="btn btn-outline-primary">
                            Next Page →
                        </a>
                    </div>
                @endif
            @else
                <div class="text-center text-muted py-4">
                    <i class="bi bi-exclamation-circle me-2"></i>No addresses found
                </div>
            @endif
        </div>
    </div>

</div>
@endsection
