@extends('layouts.app')

@section('content')
<div class="container my-5">
    <div class="card shadow rounded">
        <div class="card-header bg-primary text-white">
            <h1 class="mb-0">User Details</h1>
        </div>
        <div class="card-body">
            <!-- User Metadata -->
            <div class="mb-4">
                <h4 class="mb-3">Basic Information</h4>
                <p><strong>ID:</strong> {{ $user->id }}</p>
                <p><strong>Name:</strong> {{ $user->displayName ?? 'N/A' }}</p>
                <p><strong>Email (UPN):</strong> {{ $user->userPrincipalName ?? 'N/A' }}</p>
                <p><strong>Job Title:</strong> {{ $user->jobTitle ?? 'N/A' }}</p>
                <p><strong>Department:</strong> {{ $user->department ?? 'N/A' }}</p>
                <p><strong>Account Enabled:</strong> {{ $user->accountEnabled ? 'Yes' : 'No' }}</p>
                <p><strong>Created Date:</strong> {{ \Carbon\Carbon::parse($user->createdDateTime)->format('Y-m-d') }}</p>
            </div>

            <!-- Filter Form -->
            <form method="GET" action="{{ route('users.show', $user->id) }}" class="row g-3 mb-4">
                <div class="col-md-4">
                    <label for="range" class="form-label">Date Range</label>
                    <select name="range" id="range" class="form-select">
                        @foreach (['this_month' => 'This Month', 'last_month' => 'Last Month', 'last_3_months' => 'Last 3 Months'] as $key => $label)
                            <option value="{{ $key }}" {{ $range === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="system" class="form-label">System</label>
                    <select name="system" id="system" class="form-select">
                        @foreach (['SCM', 'Odoo', 'D365 Live', 'Fit Express', 'FIT ERP', 'Fit Express UAT', 'FITerp UAT', 'OPS', 'OPS UAT'] as $sys)
                            <option value="{{ $sys }}" {{ $system === $sys ? 'selected' : '' }}>{{ $sys }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">Apply Filters</button>
                    <a href="{{ route('users.show', $user->id) }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>

            <!-- Current Filter Display -->
            <div class="mb-4">
                <strong>Showing Sign-Ins</strong> 
                from <span class="text-primary">{{ $start->format('Y-m-d') }}</span>
                to <span class="text-primary">{{ $end->format('Y-m-d') }}</span>
                @if ($system)
                    in <span class="text-primary">{{ $system }}</span>
                @else
                    across all systems.
                @endif
            </div>

            <!-- Sign-In History -->
            <h4 class="mb-3">
                Recent Sign-Ins 
                <span class="badge bg-secondary">{{ $signIns->total() }}</span>
            </h4>

            @if ($signIns->isEmpty())
                <div class="alert alert-info">This user has no recorded sign-ins for the selected period.</div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th scope="col">Date (UTC)</th>
                                <th scope="col">System</th>
                                <th scope="col">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($signIns as $signIn)
                                <tr>
                                    <td>{{ optional($signIn->date_utc)->format('Y-m-d H:i:s') }}</td>
                                    <td>{{ $signIn->system ?? 'N/A' }}</td>
                                    <td>{{ $signIn->status ?? 'N/A' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-4">
                    {{ $signIns->withQueryString()->links() }}
                </div>
            @endif

            <div class="mt-4">
                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">← Back to Dashboard</a>
            </div>
        </div>
    </div>
</div>
@endsection
