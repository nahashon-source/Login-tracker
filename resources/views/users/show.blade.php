@extends('layouts.app')

@section('content')
<div class="container mt-5">

    <h2 class="mb-4">Employee Details</h2>

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">{{ $user->displayName }}</h5>
            <p class="mb-1"><strong>User Principal Name:</strong> {{ $user->userPrincipalName }}</p>
            <p class="mb-1"><strong>Email 1:</strong> {{ $user->mail1 ?? 'N/A' }}</p>
            <p class="mb-1"><strong>Email 2:</strong> {{ $user->mail2 ?? 'N/A' }}</p>
            <p class="mb-1"><strong>Job Title:</strong> {{ $user->jobTitle ?? 'N/A' }}</p>
            <p class="mb-1"><strong>Department:</strong> {{ $user->department ?? 'N/A' }}</p>
            <p class="mb-1"><strong>Office:</strong> {{ $user->officeLocation ?? 'N/A' }}</p>
            <p class="mb-1"><strong>Account Enabled:</strong> 
                <span class="badge {{ $user->accountEnabled ? 'bg-success' : 'bg-danger' }}">
                    {{ $user->accountEnabled ? 'Yes' : 'No' }}
                </span>
            </p>
            <p class="mb-1"><strong>Directory Synced:</strong> {{ $user->directorySynced ? 'Yes' : 'No' }}</p>
            <p class="mb-0"><strong>Created At:</strong> {{ $user->createdDateTime ?? 'N/A' }}</p>
        </div>
    </div>

    <h4>Sign In History (Last 30 Days)</h4>
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

    <form method="POST" action="{{ route('users.destroy', $user->id) }}" 
          class="mt-4"
          onsubmit="return confirm('Are you sure you want to remove this user?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger">Remove User</button>
    </form>

    <div class="mt-4">
        <a href="{{ route('dashboard') }}" class="btn btn-secondary">← Back to Dashboard</a>
    </div>

</div>
@endsection
