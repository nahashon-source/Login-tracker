<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Add New User</h1>
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        <form action="{{ route('users.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="userPrincipalName" class="form-label">User Principal Name</label>
                <input type="text" class="form-control" id="userPrincipalName" name="userPrincipalName" required>
            </div>
            <div class="mb-3">
                <label for="displayName" class="form-label">Display Name</label>
                <input type="text" class="form-control" id="displayName" name="displayName">
            </div>
            <div class="mb-3">
                <label for="mail" class="form-label">Email</label>
                <input type="email" class="form-control" id="mail" name="mail">
            </div>
            <div class="mb-3">
                <label for="accountEnabled" class="form-label">Account Enabled</label>
                <select class="form-control" id="accountEnabled" name="accountEnabled">
                    <option value="1">Yes</option>
                    <option value="0">No</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Add User</button>
            <a href="{{ route('dashboard') }}" class="btn btn-secondary">Back</a>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>