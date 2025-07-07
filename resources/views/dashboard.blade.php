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
                    <th>User</th>
                    <th>Login Count (Last 30 Days)</th>
                    <th>Login Times</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr>
                        <td>{{ $user->displayName ?? $user->userPrincipalName }}</td>
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
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{ $users->links() }}
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>