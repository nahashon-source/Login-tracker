<div class="container my-5">
    <div class="card shadow rounded">
        <div class="card-header bg-primary text-white">
            <h1 class="mb-0">User Details</h1>
        </div>
        <div class="card-body">
            <div class="mb-4">
                <p><strong>ID:</strong> {{ $user->id }}</p>
                <p><strong>Name:</strong> {{ $user->displayName ?? 'N/A' }}</p>
                <p><strong>Email (UPN):</strong> {{ $user->userPrincipalName ?? 'N/A' }}</p>
            </div>

            <h2 class="h4 mb-3">Recent Sign-Ins</h2>

            @if ($signIns->isEmpty())
                <div class="alert alert-info">No sign-in records found.</div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th scope="col">Date</th>
                                <th scope="col">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($signIns as $signIn)
                                <tr>
                                    <td>{{ $signIn->date_utc ?? 'N/A' }}</td>
                                    <td>{{ $signIn->status ?? 'N/A' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-4">
                    {{ $signIns->links() }}
                </div>
            @endif

            <div class="mt-4">
                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                    ← Back to Dashboard
                </a>
            </div>
        </div>
    </div>
</div>
