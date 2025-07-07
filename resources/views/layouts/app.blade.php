<!DOCTYPE html>
<html>
<head>
    <title>Login Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Login Tracker</a>
        <div>
            <a class="nav-link text-white" href="{{ route('activity.report') }}">Activity Report</a>
        </div>
    </div>
</nav>

<div class="container">
    @yield('content')
</div>

</body>
</html>
