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
    <div class="row g-2 mb-4" id="filter-form">
        <div class="col-md-3">
            <label for="range" class="form-label">Date Range:</label>
            <select name="range" id="range" class="form-select">
                @php
                    $selectedRange = request('range', 'this_month');
                @endphp
                <option value="this_month" {{ $selectedRange == 'this_month' ? 'selected' : '' }}>This Month</option>
                <option value="last_3_months" {{ $selectedRange == 'last_3_months' ? 'selected' : '' }}>Last 3 Months</option>
                <option value="custom" {{ $selectedRange == 'custom' ? 'selected' : '' }}>Custom Range</option>
            </select>
        </div>

        <div class="col-md-3">
            <label for="system" class="form-label">System:</label>
            <select name="system" id="system" class="form-select">
                @php
                    $selectedSystem = request('system', 'SCM');
                @endphp
                <option value="">All Systems</option>
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
            <button type="button" class="btn btn-primary me-2" id="apply">Apply</button>
            <a href="{{ route('dashboard') }}" class="btn btn-secondary">Reset</a>
        </div>
    </div>

    {{-- Filter Summary --}}
    <div id="filter-summary" class="alert alert-light" style="display: none;">
        <strong>Filters:</strong>
        <span id="range-label" class="badge bg-secondary me-1"></span>
        <span id="system-label" class="badge bg-info me-1"></span>
        <span id="search-label" class="badge bg-warning text-dark"></span>
    </div>

    {{-- Loading Indicator --}}
    <div id="loading" class="alert alert-info" style="display: none;">
        Loading data, please wait...
    </div>

    {{-- Summary Cards --}}
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-info text-white shadow">
                <div class="card-body">
                    <h5 class="card-title">Total Users</h5>
                    <p class="display-5" id="total-users">{{ $totalUsers }}</p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card bg-success text-white shadow">
                <div class="card-body">
                    <h5 class="card-title">Logged In Users</h5>
                    <p class="display-5" id="logged-in-count">{{ $loggedInCount }}</p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card bg-danger text-white shadow">
                <div class="card-body">
                    <h5 class="card-title">Not Logged In Users</h5>
                    <p class="display-5" id="not-logged-in-count">{{ $notLoggedInCount }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- User Table --}}
    <div id="user-table">
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
                <tbody id="user-table-body">
                    @foreach ($users as $user)
                        @php
                            $uniqueLoginDays = $user->signIns
                                ->pluck('date_utc')
                                ->map(fn($d) => \Carbon\Carbon::parse($d)->format('Y-m-d'))
                                ->unique()
                                ->count();
                            $missedDays = max(0, $totalDays - $uniqueLoginDays);
                        @endphp
                        <tr @if($uniqueLoginDays === 0) class="table-danger" @endif>
                            <td>{{ $user->displayName ?: 'Unknown' }}</td>
                            <td>{{ $user->userPrincipalName ?: 'Not Set' }}</td>
                            <td>{{ $missedDays }}</td>
                            <td>
                                <span class="badge bg-secondary">Count: {{ $user->sign_ins_count ?? 0 }}</span><br>
                                <small>
                                    Last: {{ optional($user->signIns->first())->date_utc
                                        ? \Carbon\Carbon::parse($user->signIns->first()->date_utc)->format('Y-m-d H:i')
                                        : 'N/A' }}
                                </small>
                            </td>
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
            <div class="mt-4">
                {{ $users->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>

<script>
    $(document).ready(function() {
        // Trigger update on Apply button click
        $('#apply').on('click', function(e) {
            e.preventDefault();
            updateDashboard();
        });
        
        // Trigger update on dropdown change
    // Trigger update on page load
    updateDashboard();

    $('#system, #range').on('change', function() {
        updateDashboard();
    });

    // Trigger update on search input (with debounce)
    let searchTimeout;
    $('#search').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(updateDashboard, 500); // 500ms debounce
    });

        function updateDashboard() {
            var range = $('#range').val();
            var system = $('#system').val();
            var search = $('#search').val();
            var rangeLabel = range === 'custom' ? 'Custom' : range.replace('_', ' ').replace(/\b\w/g, c => c.toUpperCase());
            var systemLabel = system || 'All Systems';

            console.log('Sending AJAX with:', { range, system, search }); // Debug input

            // Show loading indicator
            $('#loading').show();
            $('#user-table').html('<div class="alert alert-info">Loading...</div>');

            $.ajax({
                url: '{{ route("dashboard") }}',
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: { range: range, system: system, search: search },
                success: function(response) {
                    console.log('AJAX Response Users:', response.users);
                    $('#loading').hide();

                    // Update summary cards
                    $('#total-users').text(response.totalUsers || 0);
                    $('#logged-in-count').text(response.loggedInCount || 0);
                    $('#not-logged-in-count').text(response.notLoggedInCount || 0);

                    // Update filter summary
                    $('#range-label').text(rangeLabel).parent().toggle(!!rangeLabel);
                    $('#system-label').text(systemLabel).parent().toggle(!!system);
                    $('#search-label').text('Search: ' + (search || '')).parent().toggle(!!search);
                    $('#filter-summary').toggle(!!rangeLabel || !!system || !!search);

                    // Update user table
                    var tbody = $('#user-table-body');
                    tbody.empty();
if (response.users && response.users.length > 0) {
                        $('#user-table').html(`
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
                                <tbody id="user-table-body"></tbody>
                            </table>
                            <div class="mt-4" id="pagination"></div>
                        `);
                        response.users.forEach(function(user) {
                            var uniqueLoginDays = user.signIns
                                ? user.signIns.map(d => new Date(d.date_utc).toLocaleDateString()).filter((d, i, arr) => arr.indexOf(d) === i).length
                                : 0;
                            var missedDays = Math.max(0, response.totalDays - uniqueLoginDays);
                            var rowClass = uniqueLoginDays === 0 ? 'table-danger' : '';
                            var lastLogin = user.signIns && user.signIns.length > 0
                                ? new Date(user.signIns[0].date_utc).toLocaleString()
                                : 'N/A';

                            var userRow = '<tr class="' + rowClass + '">' +
                                '<td>' + (user.displayName || 'Unknown') + '</td>' +
                                '<td>' + (user.userPrincipalName || 'Not Set') + '</td>' +
                                '<td>' + missedDays + '</td>' +
                                '<td>' +
                                    '<span class="badge bg-secondary">Count: ' + (user.sign_ins_count || 0) + '</span><br>' +
                                    '<small>Last: ' + lastLogin + '</small>' +
                                '</td>' +
                                '<td>' +
                                    '<div class="dropdown">' +
                                        '<button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">' +
                                            'Actions' +
                                        '</button>' +
                                        '<ul class="dropdown-menu">' +
                                            '<li><a class="dropdown-item" href="/users/' + user.id + '">View</a></li>' +
                                            '<li><button class="dropdown-item text-danger" onclick="deleteUser(\'' + user.id + '\')">Delete</button></li>' +
                                        '</ul>' +
                                    '</div>' +
                                '</td>' +
                            '</tr>';
                            $('#user-table-body').append(userRow);
                        });
                        // Reinitialize Bootstrap dropdowns
                        $('.dropdown-toggle').dropdown();
                        // Append pagination links
                        $('#pagination').html(response.pagination || '');
                    } else {
                        $('#user-table').html('<div class="alert alert-info">No users found for the selected filters.</div>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error, xhr.responseText);
                    $('#loading').hide();
                    $('#total-users').text('0');
                    $('#logged-in-count').text('0');
                    $('#not-logged-in-count').text('0');
                    $('#user-table').html('<div class="alert alert-info">No users found for the selected filters.</div>');
                }
            });
        }

        // Initial load already called above
    });
</script>
@endsection