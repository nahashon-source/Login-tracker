@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2 class="mb-3">Users Not Logged In ({{ $range }})</h2>
    <a href="{{ route('dashboard') }}" class="btn btn-secondary mb-3">← Back to Dashboard</a>

    <!-- Search Form -->
    <form method="GET" action="{{ route('users.not-logged-in') }}" class="row g-2 mb-4">
        <div class="col-md-4">
            <input type="text" name="search" id="search" class="form-control"
                   placeholder="Search Name, UPN, or Email" value="{{ request('search') }}">
        </div>

        <div class="col-md-4">
            <button type="submit" class="btn btn-primary">Search</button>
            <a href="{{ route('users.not-logged-in') }}" class="btn btn-secondary">Reset</a>
        </div>
    </form>

    <!-- Active Search Term Display -->
    @if(request('search'))
        <div class="mb-2 text-muted">
            Showing results for: <strong>"{{ request('search') }}"</strong>
        </div>
    @endif

    <!-- User Table -->
    @if ($users->isEmpty())
        <div class="alert alert-info">No users found who haven't logged in for this period.</div>
    @else
        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>UserPrincipalName</th>
                    <th>Display Name</th>
                    <th>Created Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr>
                        <td>{{ $user->userPrincipalName }}</td>
                        <td>{{ $user->displayName }}</td>
                        <td>{{ \Carbon\Carbon::parse($user->createdDateTime)->format('Y-m-d') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="mt-3">
            {{ $users->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
