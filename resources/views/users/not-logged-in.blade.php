@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2 class="mb-3">Users Not Logged In ({{ $range }})</h2>
    <a href="{{ route('dashboard') }}" class="btn btn-secondary mb-3">← Back to Dashboard</a>

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
        {{ $users->withQueryString()->links() }}
    @endif
</div>
@endsection
