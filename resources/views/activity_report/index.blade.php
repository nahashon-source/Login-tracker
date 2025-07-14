@extends('layouts.app')

@section('content')
<div class="container mt-4">

    <h1 class="mb-4">User Activity Report</h1>

    

    <!-- Date Filter and Search Form -->
    <form method="GET" action="{{ route('activity.report') }}" class="mb-4">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label for="date" class="form-label">Filter by specific date:</label>
                <input type="date" name="date" id="date" class="form-control" value="{{ request('date') }}">
            </div>
            <div class="col-md-4">
                <label for="search" class="form-label">Search users:</label>
                <input type="text" name="search" id="search" class="form-control" 
                       placeholder="Search Name, UPN, or Email" value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="{{ route('activity.report') }}" class="btn btn-secondary">Clear</a>
            </div>
        </div>
    </form>

    <!-- Active Search Term Display -->
    @if(request('search'))
        <div class="mb-2 text-muted">
            Showing results for: <strong>"{{ request('search') }}"</strong>
        </div>
    @endif

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
