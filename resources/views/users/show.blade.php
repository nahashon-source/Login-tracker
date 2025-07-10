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
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($signIns as $signIn)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($signIn->date_utc)->format('D, M j, Y g:i A') }}</td>
                        <td>{{ $signIn->ip_address ?? 'N/A' }}</td>
                        <td>{{ $signIn->application ?? 'N/A' }}</td>
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
