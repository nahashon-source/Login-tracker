<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Tracker Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Login Tracker Dashboard</h1>
        <a href="{{ route('users.create') }}" class="btn btn-success mb-3">Add User</a>
        <a href="/imports" class="btn btn-primary mb-3">Import Data</a>
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        <form method="GET" action="{{ route('dashboard') }}" class="mb-3">
            <div class="row">
                <div class="col-md-4">
                    <label for="date" class="form-label">Filter by Date</label>
                    <input type="date" class="form-control" id="date" name="date" value="{{ $date ? $date->toDateString() : '' }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary mt-4">Filter</button>
                </div>
            </div>
        </form>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Surname 1</th>
                    <th>Surname 2</th>
                    <th>Email 1</th>
                    <th>Email 2</th>
                    <th>Given Name 1</th>
                    <th>Given Name 2</th>
                    <th>Login Count (Last 30 Days)</th>
                    <th>Login Times</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr>
                        <td>{{ $user->id ?? 'N/A' }}</td>
                        <td>{{ $user->displayName ?? $user->userPrincipalName }}</td>
                        <td>{{ $user->surname1 ?? 'N/A' }}</td>
                        <td>{{ $user->surname2 ?? 'N/A' }}</td>
                        <td>{{ $user->mail1 ?? 'N/A' }}</td>
                        <td>{{ $user->mail2 ?? 'N/A' }}</td>
                        <td>{{ $user->givenName1 ?? 'N/A' }}</td>
                        <td>{{ $user->givenName2 ?? 'N/A' }}</td>
                        <td>{{ $user->sign_ins_count }}</td>
                        <td>
                            @if ($user->sign_ins_count > 0)
                                <ul>
                                    @foreach ($user->signIns as $signIn)
                                        <li>{{ $signIn->date_utc }}</li>
                                    @endforeach
                                </ul>
                            @else
                                No logins in the last 30 days
                            @endif
                        </td>
                        <td>
                            @if ($user->sign_ins_count > 0)
                                Last login: {{ $user->signIns->first()->date_utc ?? 'N/A' }}
                            @else
                                Never logged in
                            @endif
                        </td>
                        <td>
                            <form action="{{ route('users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{ $users->links() }}
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>