<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Data</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">

    {{-- Page Title --}}
    <h1 class="mb-4">Import Data</h1>

    {{-- Back to Dashboard Button --}}
    <div class="mb-4">
        <a href="{{ route('dashboard') }}" class="btn btn-secondary">← Back to Dashboard</a>
    </div>

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @elseif (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Validation Errors --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Import failed due to the following errors:</strong>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Import Forms --}}
    <div class="row">
        {{-- Users Import --}}
        <div class="col-md-6">
            <h3>Import Users</h3>
            <form action="{{ route('import.users') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label for="userFile" class="form-label">Upload Users CSV</label>
                    <input type="file" class="form-control" id="userFile" name="import_file" accept=".csv" required>
                </div>
                <button type="submit" class="btn btn-primary">Import Users</button>
            </form>
        </div>

        {{-- Sign-Ins Import --}}
        <div class="col-md-6">
            <h3>Import Sign-Ins</h3>
            <form action="{{ route('import.sign_ins') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label for="signInFile" class="form-label">Upload Sign-Ins CSV</label>
                    <input type="file" class="form-control" id="signInFile" name="import_file" accept=".csv" required>
                </div>
                <button type="submit" class="btn btn-primary">Import Sign-Ins</button>
            </form>
        </div>
    </div>

    {{-- Applications Import --}}
    <div class="row mt-4">
        <div class="col-md-12">
            <h3>Import Applications</h3>
            <p class="text-muted">Upload a CSV file with columns: <strong>userPrincipalName</strong> and <strong>system</strong></p>
            <form action="{{ route('import.applications') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label for="applicationFile" class="form-label">Upload Applications CSV</label>
                    <input type="file" class="form-control" id="applicationFile" name="import_file" accept=".csv" required>
                </div>
                <button type="submit" class="btn btn-success">Import Applications</button>
            </form>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
