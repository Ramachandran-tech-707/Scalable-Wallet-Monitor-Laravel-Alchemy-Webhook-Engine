@extends('layouts.app')

@section('content')
<div class="container mt-5">

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            <strong>There were some issues:</strong>
            <ul class="mb-0 mt-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-4 justify-content-center">

        {{-- CREATE --}}
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-success text-white d-flex align-items-center">
                    <i class="bi bi-plus-circle me-2 fs-5"></i>
                    <h5 class="mb-0">Create New Variable</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('variables.create') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Variable Name</label>
                            <input type="text" name="variable_name" class="form-control" placeholder="e.g. userWallets" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">CSV File (wallet addresses)</label>
                            <input type="file" name="csv_file" class="form-control" accept=".csv,.txt" required>
                            <small class="text-muted">Max size: 50MB. All addresses must be in the file, separated by commas or lines.</small>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-cloud-upload me-1"></i> Create & Sync
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- UPDATE --}}
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-warning text-dark d-flex align-items-center">
                    <i class="bi bi-pencil-square me-2 fs-5"></i>
                    <h5 class="mb-0">Add / Delete Addresses in Variable</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('variables.update') }}">
                        @csrf
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
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Addresses to Add</label>
                            <textarea name="add_addresses" class="form-control" rows="3"
                                placeholder="Enter comma-separated addresses"></textarea>
                            <small class="text-muted">Example: 0x123..., 0x456..., 0x789...</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Addresses to Delete</label>
                            <textarea name="delete_addresses" class="form-control" rows="3"
                                placeholder="Enter comma-separated addresses"></textarea>
                            <small class="text-muted">Example: 0xabc..., 0xdef...</small>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-warning">
                                <i class="bi bi-arrow-repeat me-1"></i> Update Variable
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- DELETE VARIABLE --}}
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-danger text-white d-flex align-items-center">
                    <i class="bi bi-trash me-2 fs-5"></i>
                    <h5 class="mb-0">Delete Variable</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('variables.delete') }}">
                        @csrf
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
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-danger">
                                <i class="bi bi-dash-circle me-1"></i> Delete Variable
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
