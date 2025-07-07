@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2 class="mb-3">Logged In Users ({{ ucfirst(str_replace('_', ' ', $range)) }})</h2>
    <form method="GET" class="row g-2 mb-3">
        <div class="col-auto">
            <input type="text" name="search" class="form-control"
                   placeholder="Search name, UPN or email" value="{{ request('search') }}">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary">Search</button>
            <a href="{{ route(Route::currentRouteName()) }}" class="btn btn-secondary">Reset</a>
        </div>
     </form>


    <div class="mb-3">
        <a href="{{ route('dashboard') }}" class="btn btn-secondary">← Back to Dashboard</a>
    </div>

    @if ($users->isEmpty())
        <div class="alert alert-info">No users logged in during this period.</div>
    @else
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
                        <td>{{ $users->firstItem() + $index }}</td>
                        <td>{{ $user->userPrincipalName }}</td>
                        <td>{{ $user->displayName ?? 'N/A' }}</td>
                        <td><span class="badge bg-success">{{ $user->login_count }}</span></td>
                        <td>
                            <a href="{{ route('users.show', $user->id) }}" class="btn btn-sm btn-primary">Details</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-3">
            {{ $users->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
