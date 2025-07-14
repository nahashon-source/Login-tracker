@extends('layouts.app')

@section('content')
<div class="container mt-5">

    {{-- Page Heading --}}
    <h2 class="mb-4">Employee Details</h2>

    {{-- User Details Card --}}
    <div class="card mb-4">
        <div class="card-body">

            <h5 class="card-title mb-4">{{ $user->displayName }}</h5>

            <div class="row">
                <div class="col-md-4 fw-bold">User Principal Name</div>
                <div class="col-md-8">{{ $user->userPrincipalName }}</div>

                <div class="col-md-4 fw-bold">Email 1</div>
                <div class="col-md-8">{{ $user->mail1 ?? 'N/A' }}</div>

                <div class="col-md-4 fw-bold">Email 2</div>
                <div class="col-md-8">{{ $user->mail2 ?? 'N/A' }}</div>

                <div class="col-md-4 fw-bold">Job Title</div>
                <div class="col-md-8">{{ $user->jobTitle ?? 'N/A' }}</div>

                <div class="col-md-4 fw-bold">Department</div>
                <div class="col-md-8">{{ $user->department ?? 'N/A' }}</div>

                <div class="col-md-4 fw-bold">Office Location</div>
                <div class="col-md-8">{{ $user->officeLocation ?? 'N/A' }}</div>

                <div class="col-md-4 fw-bold">Account Enabled</div>
                <div class="col-md-8">
                    <span class="badge {{ $user->accountEnabled ? 'bg-success' : 'bg-danger' }}">
                        {{ $user->accountEnabled ? 'Yes' : 'No' }}
                    </span>
                </div>

                <div class="col-md-4 fw-bold">Directory Synced</div>
                <div class="col-md-8">{{ $user->directorySynced ? 'Yes' : 'No' }}</div>

                <div class="col-md-4 fw-bold">Created At</div>
                <div class="col-md-8">{{ $user->createdDateTime ? \Carbon\Carbon::parse($user->createdDateTime)->format('Y-m-d') : 'N/A' }}</div>
            </div>

        </div>
    </div>

    {{-- Recent Applications Used --}}
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Recent Applications Used</h5>
        </div>
        <div class="card-body">
            @if ($recentApplications->isEmpty())
                <div class="alert alert-info">No recent application usage found.</div>
            @else
                <div class="row">
                    @foreach ($recentApplications as $app)
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h6 class="card-title">{{ $app->application ?? 'Unknown App' }}</h6>
                                    <p class="card-text">
                                        <small class="text-muted">System: {{ $app->system ?? 'N/A' }}</small><br>
                                        <span class="badge bg-primary">{{ $app->usage_count }} logins</span><br>
                                        <small class="text-muted">Last used: {{ \Carbon\Carbon::parse($app->last_used)->format('M j, Y g:i A') }}</small>
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Sign In History Table --}}
    <h4 class="mb-3">Sign In History (Last 30 Days)</h4>

    @if ($signIns->isEmpty())
        <div class="alert alert-info">No login history available.</div>
    @else
        <table class="table table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Date</th>
                    <th>IP Address</th>
                    <th>Application</th>
                    <th>System</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($signIns as $signIn)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($signIn->date_utc)->format('D, M j, Y g:i A') }}</td>
                        <td>{{ $signIn->ip_address ?? 'N/A' }}</td>
                        <td>
                            <span class="fw-bold">{{ $signIn->application ?? 'N/A' }}</span>
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ $signIn->system ?? 'N/A' }}</span>
                        </td>
                        <td>
                            <span class="badge {{ $signIn->status === 'Success' ? 'bg-success' : 'bg-danger' }}">
                                {{ $signIn->status }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- Remove User Form --}}
    <form method="POST" action="{{ route('users.destroy', ['user' => $user->id]) }}" class="mt-4"
          onsubmit="return confirm('Are you sure you want to remove this user?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger">Remove User</button>
    </form>

    {{-- Back to Dashboard --}}
    <div class="mt-4">
        <a href="{{ route('dashboard') }}" class="btn btn-secondary">← Back to Dashboard</a>
    </div>

</div>
@endsection
