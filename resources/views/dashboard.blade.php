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
    <h1 class="mb-4">Login Tracker Dashboard</h1>

    <div class="mb-3">
        <a href="{{ route('users.create') }}" class="btn btn-success">Add User</a>
        <a href="/imports" class="btn btn-primary">Import Data</a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @elseif (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <!-- Filters -->
    <form method="GET" action="{{ route('dashboard') }}" class="mb-4">
        <div class="row g-3 align-items-center">
            <div class="col-md-3">
                <label for="range" class="form-label">Date Range:</label>
                <select name="range" id="range" class="form-select">
                    <option value="">-- Select Range --</option>
                    <option value="this_month" {{ request('range') == 'this_month' ? 'selected' : '' }}>This Month</option>
                    <option value="last_month" {{ request('range') == 'last_month' ? 'selected' : '' }}>Last Month</option>
                    <option value="last_3_months" {{ request('range') == 'last_3_months' ? 'selected' : '' }}>Last 3 Months</option>
                </select>
            </div>

            <div class="col-md-3">
                <label for="system" class="form-label">System:</label>
                <select name="system" id="system" class="form-select">
                    <option value="">-- All Systems --</option>
                    @foreach (['SCM', 'Odoo', 'D365 Live', 'Fit Express', 'FIT ERP', 'Fit Express UAT', 'FITerp UAT', 'OPS', 'OPS UAT'] as $sys)
                        <option value="{{ $sys }}" {{ request('system') == $sys ? 'selected' : '' }}>{{ $sys }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3 align-self-end">
                <button type="submit" class="btn btn-primary">Apply Filters</button>
                <a href="{{ route('dashboard') }}" class="btn btn-secondary">Reset</a>
            </div>
        </div>
    </form>

    @if ($users->isEmpty())
        <div class="alert alert-info">No users found. Please import users or check the database.</div>
    @else
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email (UPN)</th>
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
                        <td>
                            <span class="badge bg-secondary">Count: {{ $user->login_count ?? 0 }}</span><br>
                            <small>Last: {{ $user->last_login_at?->format('Y-m-d') ?? 'N/A' }}</small>
                        </td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    Actions
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('users.show', $user->id) }}">View</a>
                                    </li>
                                    <li>
                                        <form action="{{ route('users.destroy', $user->id) }}" method="POST"
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

        <!-- Pagination -->
        <div class="mt-4">
            {{ $users->withQueryString()->links() }}
        </div>
    @endif
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
