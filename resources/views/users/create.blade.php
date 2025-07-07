<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1 class="mb-4">Add New User</h1>

    <!-- Success Message -->
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <!-- Validation Errors -->
    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Whoops!</strong> Please fix the following issues:
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Form Start -->
    <form action="{{ route('users.store') }}" method="POST">
        @csrf

        <!-- ID -->
        <div class="mb-3">
            <label for="id" class="form-label">ID (e.g., GUID, required)</label>
            <input type="text" class="form-control" id="id" name="id" value="{{ old('id') }}" required>
        </div>

        <!-- User Principal Name -->
        <div class="mb-3">
            <label for="userPrincipalName" class="form-label">User Principal Name</label>
            <input type="text" class="form-control" id="userPrincipalName" name="userPrincipalName" value="{{ old('userPrincipalName') }}" required>
        </div>

        <!-- Display Name -->
        <div class="mb-3">
            <label for="displayName" class="form-label">Display Name</label>
            <input type="text" class="form-control" id="displayName" name="displayName" value="{{ old('displayName') }}">
        </div>

        <!-- Surname 1 -->
        <div class="mb-3">
            <label for="surname1" class="form-label">Surname 1</label>
            <input type="text" class="form-control" id="surname1" name="surname1" value="{{ old('surname1') }}">
        </div>

        <!-- Surname 2 -->
        <div class="mb-3">
            <label for="surname2" class="form-label">Surname 2</label>
            <input type="text" class="form-control" id="surname2" name="surname2" value="{{ old('surname2') }}">
        </div>

        <!-- Email 1 -->
        <div class="mb-3">
            <label for="mail1" class="form-label">Email 1</label>
            <input type="email" class="form-control" id="mail1" name="mail1" value="{{ old('mail1') }}">
        </div>

        <!-- Email 2 -->
        <div class="mb-3">
            <label for="mail2" class="form-label">Email 2</label>
            <input type="email" class="form-control" id="mail2" name="mail2" value="{{ old('mail2') }}">
        </div>

        <!-- Given Name 1 -->
        <div class="mb-3">
            <label for="givenName1" class="form-label">Given Name 1</label>
            <input type="text" class="form-control" id="givenName1" name="givenName1" value="{{ old('givenName1') }}">
        </div>

        <!-- Given Name 2 -->
        <div class="mb-3">
            <label for="givenName2" class="form-label">Given Name 2</label>
            <input type="text" class="form-control" id="givenName2" name="givenName2" value="{{ old('givenName2') }}">
        </div>

        <!-- Account Enabled -->
        <div class="mb-3">
            <label for="accountEnabled" class="form-label">Account Enabled</label>
            <select class="form-control" id="accountEnabled" name="accountEnabled">
                <option value="1" {{ old('accountEnabled') == '1' ? 'selected' : '' }}>Yes</option>
                <option value="0" {{ old('accountEnabled') == '0' ? 'selected' : '' }}>No</option>
            </select>
        </div>

        <!-- Submit and Back Buttons -->
        <button type="submit" class="btn btn-primary">Add User</button>
        <a href="{{ route('dashboard') }}" class="btn btn-secondary">Back to Dashboard</a>
    </form>
    <!-- Form End -->

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
