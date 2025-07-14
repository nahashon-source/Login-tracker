@extends('layouts.app')

@section('content')
<div class="container mt-5">

    {{-- Page Heading --}}
    <h2 class="mb-4">Employee Details</h2>

    {{-- User Details Card --}}
    <div class="card mb-4">
        <div class="card-body">

            <h5 class="card-title mb-4">{{ $user->displayName }}</h5>

            <div class="row">
                <!-- First Column -->
                <div class="col-md-6">
                    <div class="row mb-3">
                        <div class="col-md-5 fw-bold">User Principal Name</div>
                        <div class="col-md-7">{{ $user->displayName ?? 'N/A' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-5 fw-bold">Email</div>
                        <div class="col-md-7">{{ $user->userPrincipalName ?? 'N/A' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-5 fw-bold">Job Title</div>
                        <div class="col-md-7">{{ $user->jobTitle ?? 'N/A' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-5 fw-bold">Department</div>
                        <div class="col-md-7">{{ $user->department ?? 'N/A' }}</div>
                    </div>
                </div>

                <!-- Second Column -->
                <div class="col-md-6">
                    <div class="row mb-3">
                        <div class="col-md-5 fw-bold">Office Location</div>
                        <div class="col-md-7">{{ $user->officeLocation ?? 'N/A' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-5 fw-bold">Account Enabled</div>
                        <div class="col-md-7">
                            <span class="badge {{ $user->accountEnabled ? 'bg-success' : 'bg-danger' }}">
                                {{ $user->accountEnabled ? 'Yes' : 'No' }}
                            </span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-5 fw-bold">Directory Synced</div>
                        <div class="col-md-7">{{ $user->directorySynced ? 'Yes' : 'No' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-5 fw-bold">Created At</div>
                        <div class="col-md-7">{{ $user->createdDateTime ? \Carbon\Carbon::parse($user->createdDateTime)->format('Y-m-d') : 'N/A' }}</div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- Recent Applications Used --}}
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Recent Applications Used</h5>
        </div>
        <div class="card-body">
            @if ($recentApplications->isEmpty())
                <div class="alert alert-info">No recent application usage found.</div>
            @else
                <div class="row">
                @foreach ($recentApplications as $app)
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title fs-5">{{ $app->application ?? 'Unknown App' }}</h5>
                                <p class="card-text fs-6">
                                    <span class="text-muted">Systems: {{ $app->systems ?? 'N/A' }}</span><br>
                                    <span class="badge bg-primary fs-6">{{ $app->usage_count }} logins</span><br>
                                    <span class="text-muted">Last used: {{ \Carbon\Carbon::parse($app->last_used)->format('M j, Y g:i A') }}</span>
                                </p>
                            </div>
                        </div>
                    </div>
                @endforeach
                </div>
            @endif
        </div>
    </div>



    {{-- Sign In History Table --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Sign In History (Last 30 Days)</h4>
        <div class="d-flex gap-3">
            {{-- Search Field --}}
            <div class="input-group" style="width: 300px;">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" class="form-control" id="applicationSearch" placeholder="Search applications...">
            </div>
            {{-- Calendar --}}
            <div class="input-group" style="width: 250px;">
                <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                <input type="date" class="form-control" id="dateFilter">
                <button class="btn btn-outline-secondary" type="button" id="clearDate" title="Clear date filter">Clear</button>
            </div>
        </div>
    </div>

    @if ($signIns->isEmpty())
        <div class="alert alert-info">No login history available.</div>
    @else
        <table class="table table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Date</th>
                    <th>IP Address</th>
                    <th>Application</th>
                    <th>System</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="signInTableBody">
                @foreach ($signIns as $signIn)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($signIn->date_utc)->format('D, M j, Y g:i A') }}</td>
                        <td>{{ $signIn->ip_address ?? 'N/A' }}</td>
                        <td>
                            <span class="fw-bold">{{ $signIn->application ?? 'N/A' }}</span>
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ $signIn->system ?? 'N/A' }}</span>
                        </td>
                        <td>
                            <span class="badge {{ $signIn->status === 'Success' ? 'bg-success' : 'bg-danger' }}">
                                {{ $signIn->status }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        
        {{-- Pagination --}}
        <div class="d-flex justify-content-center mt-4">
            {{ $signIns->withQueryString()->links() }}
        </div>
    @endif


    {{-- Back to Dashboard --}}
    <div class="mt-4">
        <a href="{{ route('dashboard') }}" class="btn btn-secondary">← Back to Dashboard</a>
    </div>

</div>

<script>
    // Search functionality for applications
    document.getElementById('applicationSearch').addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const tableRows = document.querySelectorAll('#signInTableBody tr');
        
        tableRows.forEach(row => {
            const applicationCell = row.cells[2].textContent.toLowerCase();
            if (searchTerm === '' || applicationCell.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
    
    // Date filter functionality
    document.getElementById('dateFilter').addEventListener('change', function() {
        if (this.value) {
            const selectedDate = new Date(this.value);
            const tableRows = document.querySelectorAll('#signInTableBody tr');
            
            tableRows.forEach(row => {
                const dateCell = row.cells[0].textContent;
                const rowDate = new Date(dateCell);
                
                if (rowDate.toDateString() === selectedDate.toDateString()) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
    });
    
    // Clear date filter
    document.getElementById('clearDate').addEventListener('click', function() {
        document.getElementById('dateFilter').value = '';
        const tableRows = document.querySelectorAll('#signInTableBody tr');
        tableRows.forEach(row => {
            row.style.display = '';
        });
    });
</script>

@endsection
