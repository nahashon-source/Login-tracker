@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Not Logged In Users</h1>
        <a href="{{ route('dashboard') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    {{-- Filters --}}
    <div class="row g-2 mb-4">
        <div class="col-md-3">
            <label for="range" class="form-label">Date Range:</label>
            <select name="range" id="range" class="form-select">
                <option value="this_month" {{ $filters['range'] == 'this_month' ? 'selected' : '' }}>This Month</option>
                <option value="last_month" {{ $filters['range'] == 'last_month' ? 'selected' : '' }}>Last Month</option>
                <option value="last_3_months" {{ $filters['range'] == 'last_3_months' ? 'selected' : '' }}>Last 3 Months</option>
            </select>
        </div>

        <div class="col-md-3">
            <label for="system" class="form-label">System:</label>
            <select name="system" id="system" class="form-select">
                @foreach ($systems ?? [] as $sys)
                    <option value="{{ $sys }}" {{ $filters['system'] == $sys ? 'selected' : '' }}>{{ $sys }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3">
            <label for="search" class="form-label">Search:</label>
            <input type="text" name="search" id="search" class="form-control" 
                   placeholder="Name or Email" value="{{ $search }}">
        </div>

        <div class="col-md-3 d-flex align-items-end">
            <button type="button" class="btn btn-primary me-2" onclick="applyFilters()">Apply</button>
            <a href="{{ route('users.not-logged-in') }}" class="btn btn-secondary">Reset</a>
        </div>
    </div>

    {{-- Filter Summary --}}
    <div class="alert alert-warning mb-4">
        <strong>Showing:</strong> Users who have NOT logged into <span class="badge bg-primary">{{ $filters['system'] }}</span> 
        during <span class="badge bg-secondary">{{ $filters['rangeLabel'] }}</span>
        @if($search)
            matching <span class="badge bg-warning text-dark">"{{ $search }}"</span>
        @endif
    </div>

    {{-- Users Table --}}
    @if ($users->isEmpty())
        <div class="alert alert-success">
            <h5>Great news!</h5>
            <p class="mb-0">All users assigned to {{ $filters['system'] }} have logged in during {{ $filters['rangeLabel'] }}.</p>
        </div>
    @else
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 text-danger">{{ $users->total() }} Users Have Not Logged In</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Last System Login</th>
                                <th>Account Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                <tr class="table-warning">
                                    <td>
                                        <strong>{{ $user->displayName ?: 'Unknown' }}</strong>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $user->userPrincipalName }}</small>
                                    </td>
                                    <td>
                                        <small class="text-muted">No login in selected period</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning text-dark">Inactive</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('users.show', $user->id) }}" class="btn btn-sm btn-primary">
                                            View Details
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Pagination --}}
        <div class="mt-4">
            {{ $users->withQueryString()->links() }}
        </div>
    @endif
</div>

<script>
function applyFilters() {
    const range = document.getElementById('range').value;
    const system = document.getElementById('system').value;
    const search = document.getElementById('search').value;
    
    const params = new URLSearchParams();
    if (range) params.set('range', range);
    if (system) params.set('system', system);
    if (search) params.set('search', search);
    
    window.location.href = '{{ route("users.not-logged-in") }}?' + params.toString();
}

// Allow Enter key to trigger search
document.getElementById('search').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        applyFilters();
    }
});
</script>
@endsection
