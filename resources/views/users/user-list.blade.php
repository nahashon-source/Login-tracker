@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4">{{ $title }}</h2>

    <a href="{{ route('dashboard') }}" class="btn btn-secondary mb-3">← Back to Dashboard</a>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Display Name</th>
                <th>User Principal Name</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
            <tr>
                <td>{{ $user->id }}</td>
                <td>{{ $user->displayName }}</td>
                <td>{{ $user->userPrincipalName }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="3">No users found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-4">
        {{ $users->links() }}
    </div>
</div>
@endsection
