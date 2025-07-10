<form action="{{ route('import.users') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <input type="file" name="file" accept=".csv,.xls,.xlsx">
    <button type="submit" class="btn btn-primary">Import Users</button>
</form>
