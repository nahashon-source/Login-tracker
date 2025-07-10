@extends('layouts.app')

@section('content')
<div class="container mt-4">

    {{-- Page Title --}}
    <h1 class="mb-4">Login Tracker Dashboard</h1>

    {{-- Action Buttons --}}
    <div class="mb-3">
        <a href="{{ route('imports.index') }}" class="btn btn-primary">Import Data</a>
        <a href="{{ route('activity.report') }}" class="btn btn-info">Activity Report</a>
    </div>

    {{-- Flash Messages --}}
    @include('partials.alerts')

    {{-- Filters & Search Form --}}
    <form method="GET" action="{{ route('dashboard') }}" class="row g-2 mb-4">
        <div class="col-md-3">
            <label for="range" class="form-label">Date Range:</label>
            <select name="range" id="range" class="form-select">
                <option value="this_month" {{ $rangeInput === 'this_month' ? 'selected' : '' }}>This Month</option>
                <option value="last_month" {{ $rangeInput === 'last_month' ? 'selected' : '' }}>Last Month</option>
                <option value="last_3_months" {{ $rangeInput === 'last_3_months' ? 'selected' : '' }}>Last 3 Months</option>
            </select>
        </div>

        <div class="col-md-3">
            <label for="search" class="form-label">Search:</label>
            <input type="text" name="search" id="search" class="form-control"
                   placeholder="Name, UPN, or Email" value="{{ request('search') }}">
        </div>

        <div class="col-md-3 d-flex align-items-end">
            <button type="submit" class="btn btn-primary me-2">Apply</button>
            <a href="{{ route('dashboard') }}" class="btn btn-secondary">Reset</a>
        </div>
    </form>

    {{-- Summary Cards --}}
    <div class="row mb-4">
        <div class="col-md-6">
            <a href="{{ route('users.logged-in', ['range' => $rangeLabel]) }}" class="text-decoration-none">
                <div class="card bg-success text-white shadow">
                    <div class="card-body">
                        <h5 class="card-title">Logged In Users</h5>
                        <p class="display-4">{{ $loggedInCount }}</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-6">
            <a href="{{ route('users.not-logged-in', ['range' => $rangeLabel]) }}" class="text-decoration-none">
                <div class="card bg-danger text-white shadow">
                    <div class="card-body">
                        <h5 class="card-title">Not Logged In Users</h5>
                        <p class="display-4">{{ $notLoggedInCount }}</p>
                    </div>
                </div>
            </a>
        </div>
    </div>

    {{-- User Table --}}
    @if ($users->isEmpty())
        <div class="alert alert-info">No users found. Please import users or check the database.</div>
    @else
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email (UPN)</th>
                    <th>No Logins</th>
                    <th>Logins</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->displayName ?? 'N/A' }}</td>
                        <td>{{ $user->userPrincipalName ?? 'N/A' }}</td>
                        <td>{{ $totalDays - ($user->sign_ins_count ?? 0) }}</td>
                        <td>
                            <span class="badge bg-secondary">Count: {{ $user->sign_ins_count ?? 0 }}</span><br>
                            <small>
                                Last: {{ $user->lastLogin ? \Carbon\Carbon::parse($user->lastLogin->date_utc)->format('Y-m-d') : 'N/A' }}
                            </small>
                        </td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    Actions
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('users.show', ['user' => $user->id]) }}">View</a>
                                    </li>
                                    <li>
                                        <form action="{{ route('users.destroy', ['user' => $user->id]) }}" method="POST"
                                              onsubmit="return confirm('Are you sure you want to delete this user?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger">Delete</button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-4">
            {{ $users->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
