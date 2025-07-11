@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h1 class="mb-4">Login Tracker Dashboard</h1>

    <div class="mb-3">
        <a href="{{ route('imports.index') }}" class="btn btn-primary">Import Data</a>
        <a href="{{ route('activity.report') }}" class="btn btn-info">Activity Report</a>
    </div>

    @include('partials.alerts')

    {{-- Filters --}}
    <form method="GET" action="{{ route('dashboard') }}" class="row g-2 mb-4">
        <div class="col-md-3">
            <label for="range" class="form-label">Date Range:</label>
            <select name="range" id="range" class="form-select">
                @php
                    $selectedRange = request('range', 'this_month');
                @endphp
                <option value="this_month" {{ $selectedRange == 'this_month' ? 'selected' : '' }}>This Month</option>
                <option value="last_month" {{ $selectedRange == 'last_month' ? 'selected' : '' }}>Last Month</option>
                <option value="last_3_months" {{ $selectedRange == 'last_3_months' ? 'selected' : '' }}>Last 3 Months</option>
            </select>
        </div>

        <div class="col-md-3">
            <label for="system" class="form-label">System:</label>
            <select name="system" id="system" class="form-select">
                @php
                    $selectedSystem = request('system', 'D365 Live');
                @endphp
                @foreach ($systems ?? [] as $sys)
                    <option value="{{ $sys }}" {{ $selectedSystem == $sys ? 'selected' : '' }}>{{ $sys }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3">
            <label for="search" class="form-label">Search:</label>
            <input type="text" name="search" id="search" class="form-control"
                   placeholder="Name or UPN" value="{{ request('search') }}">
        </div>

        <div class="col-md-3 d-flex align-items-end">
            <button type="submit" class="btn btn-primary me-2">Apply</button>
            <a href="{{ route('dashboard') }}" class="btn btn-secondary">Reset</a>
        </div>
    </form>

    {{-- Summary Cards --}}
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-info text-white shadow">
                <div class="card-body">
                    <h5 class="card-title">Total Users</h5>
                    <p class="display-5">{{ \App\Models\User::count() }}</p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card bg-success text-white shadow">
                <div class="card-body">
                    <h5 class="card-title">Logged In Users</h5>
                    <p class="display-5">{{ $loggedInCount }}</p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card bg-danger text-white shadow">
                <div class="card-body">
                    <h5 class="card-title">Not Logged In Users</h5>
                    <p class="display-5">{{ $notLoggedInCount }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- User Table --}}
    @if ($users->isEmpty())
        <div class="alert alert-info">No users found for the selected filters.</div>
    @else
        <table class="table table-striped table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Name</th>
                    <th>Email (UPN)</th>
                    <th>Missed Days</th>
                    <th>Logins</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr>
                        {{-- Display Name --}}
                        <td>{{ $user->displayName ?: 'Unknown' }}</td>

                        {{-- UPN --}}
                        <td>{{ $user->userPrincipalName ?: 'Not Set' }}</td>

                        {{-- Missed Days (whole numbers only) --}}
                        <td>
                            @if ($user->signIns->isNotEmpty())
                                {{ max(0, intval($totalDays - $user->sign_ins_count)) }}
                            @else
                                {{ intval($totalDays) }}
                            @endif
                        </td>

                        {{-- Logins --}}
                        <td>
                            <span class="badge bg-secondary">Count: {{ $user->sign_ins_count ?? 0 }}</span><br>
                            <small>
                                Last:
                                {{ optional($user->signIns->first())->date_utc
                                    ? \Carbon\Carbon::parse($user->signIns->first()->date_utc)->format('Y-m-d H:i')
                                    : 'N/A' }}
                            </small>
                        </td>

                        {{-- Actions --}}
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    Actions
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('users.show', $user->id) }}">View</a></li>
                                    <li>
                                        <form action="{{ route('users.destroy', $user->id) }}" method="POST"
                                              onsubmit="return confirm('Delete this user?');">
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

        {{-- Pagination --}}
        <div class="mt-4">
            {{ $users->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
