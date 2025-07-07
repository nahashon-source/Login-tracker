@extends('layouts.app')

@section('content')
<div class="container mt-4">

    <h1 class="mb-4">User Activity Report</h1>

    <div class="mb-3">
        <a href="{{ route('dashboard') }}" class="btn btn-secondary">← Back to Dashboard</a>
    </div>

    <!-- Date Filter Form -->
    <form method="GET" action="{{ route('activity.report') }}" class="mb-4">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label for="date" class="form-label">Filter by specific date:</label>
                <input type="date" name="date" id="date" class="form-control" value="{{ request('date') }}">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="{{ route('activity.report') }}" class="btn btn-secondary">Clear</a>
            </div>
        </div>
    </form>

    <p>
        <strong>Showing activity from:</strong> {{ $startDate->toDateString() }} to {{ $endDate->toDateString() }}
    </p>

    <!-- Activity Table -->
    <div class="table-responsive">
        <table class="table table-bordered table-hover table-striped">
            <thead class="table-dark">
                <tr>
                    <th>UserPrincipalName</th>
                    <th>Display Name</th>
                    <th>Logins</th>
                    <th>First Login</th>
                    <th>Last Login</th>
                </tr>
            </thead>
            <tbody>
                @forelse($activity as $user)
                    <tr>
                        <td>{{ $user->userPrincipalName }}</td>
                        <td>{{ $user->displayName }}</td>
                        <td><span class="badge bg-primary">{{ $user->login_count }}</span></td>
                        <td>{{ $user->first_login ?? 'No Activity' }}</td>
                        <td>{{ $user->last_login ?? 'No Activity' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">No user activity found for this period.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $activity->withQueryString()->links() }}
        </div>
    </div>

</div>
@endsection
