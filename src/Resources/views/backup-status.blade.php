<!DOCTYPE html>
<html>
<head>
    <title>Backup Status</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .btn { padding: 5px 10px; text-decoration: none; color: white; border-radius: 3px; }
        .btn-success { background-color: #28a745; }
        .btn-danger { background-color: #dc3545; }
        .btn-info { background-color: #17a2b8; }
    </style>
</head>
<body>
<h1>Backup Status</h1>

<form action="{{ route('backups.store') }}" method="POST" style="margin-bottom: 20px;">
    @csrf
    <button type="submit" class="btn btn-success">Create New Backup</button>
</form>

<table>
    <thead>
    <tr>
        <th>Backup ID</th>
        <th>Date</th>
        <th>Size</th>
        <th>Files</th>
        <th>Actions</th>
    </tr>
    </thead>
    <tbody>
    @foreach($backups as $backup)
        <tr>
            <td>{{ $backup['id'] }}</td>
            <td>{{ $backup['date']->format('Y-m-d H:i:s') }}</td>
            <td>{{ $backup['size'] }}</td>
            <td>
                <ul>
                    @foreach($backup['files'] as $file)
                        <li>{{ basename($file) }}</li>
                    @endforeach
                </ul>
            </td>
            <td>
                <form action="{{ route('backups.restore', $backup['id']) }}" method="POST" style="display: inline;">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-info">Restore</button>
                </form>

                <a href="{{ route('backups.download', $backup['id']) }}" class="btn btn-success">Download</a>

                <form action="{{ route('backups.destroy', $backup['id']) }}" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                </form>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
</body>
</html>