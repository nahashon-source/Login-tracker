@extends('layouts.app')

@section('content')
<div class="container mt-4">

    <h2 class="mb-4">User Activity Report</h2>

    <!-- Date Filter Form -->
    <form method="GET" action="{{ route('activity.report') }}" class="mb-4">
        <div class="row g-2 align-items-end">
            <div class="col-auto">
                <label for="date" class="form-label">Filter by specific date:</label>
                <input type="date" name="date" id="date" class="form-control" value="{{ request('date') }}">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="{{ route('activity.report') }}" class="btn btn-secondary">Clear</a>
            </div>
        </div>
    </form>

    <p><strong>Showing activity from:</strong> {{ $startDate->toDateString() }} to {{ $endDate->toDateString() }}</p>

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
                        <td>{{ $user->login_count }}</td>
                        <td>{{ $user->first_login ?? 'No Activity' }}</td>
                        <td>{{ $user->last_login ?? 'No Activity' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">No user activity found for this period.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection
