@extends('layouts.app')

@section('content')
<div class="container mt-4">

    {{-- Page Title with Date Range --}}
    <h2 class="mb-3">Logged In Users ({{ ucfirst(str_replace('_', ' ', $range)) }})</h2>

    {{-- Search Form --}}
    <form method="GET" action="{{ route('users.logged-in') }}" class="row g-2 mb-3">
        {{-- Hidden Range Input --}}
        <input type="hidden" name="range" value="{{ $range }}">

        {{-- Search input --}}
        <div class="col-md-4">
            <input type="text" name="search" class="form-control"
                   placeholder="Search Name, UPN, or Email" value="{{ request('search') }}">
        </div>

        {{-- Search and Reset buttons --}}
        <div class="col-md-4">
            <button type="submit" class="btn btn-primary">Search</button>
            <a href="{{ route('users.logged-in', ['range' => $range]) }}" class="btn btn-secondary">Reset</a>
        </div>
    </form>

    {{-- Active Search Term Display --}}
    @if(request('search'))
        <div class="mb-2 text-muted">
            Showing results for: <strong>"{{ request('search') }}"</strong>
        </div>
    @endif

    {{-- Back to Dashboard --}}
    <div class="mb-3">
        <a href="{{ route('dashboard', ['range' => $range]) }}" class="btn btn-secondary">← Back to Dashboard</a>
    </div>

    {{-- No Results Found --}}
    @if ($users->isEmpty())
        <div class="alert alert-info">No users logged in during this period.</div>
    @else
        {{-- Users Table --}}
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>UserPrincipalName</th>
                    <th>Display Name</th>
                    <th>Login Count</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $index => $user)
                    <tr>
                        {{-- Serial Number --}}
                        <td>{{ $users->firstItem() + $index }}</td>
                        <td>{{ $user->userPrincipalName ?? 'N/A' }}</td>
                        <td>{{ $user->displayName ?? 'N/A' }}</td>
                        <td>
                            <span class="badge bg-success">{{ $user->login_count ?? 0 }}</span>
                        </td>
                        <td>
                        <a href="{{ route('users.show', ['user' => $user->id, 'range' => $range]) }}" class="btn btn-sm btn-primary">Details</a>

                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Pagination Links --}}
        <div class="mt-3">
            {{ $users->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
