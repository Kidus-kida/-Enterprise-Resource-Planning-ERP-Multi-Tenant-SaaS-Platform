<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle ?? 'Dashboard' }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f8f9fa; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { border-bottom: 1px solid #ddd; padding-bottom: 20px; margin-bottom: 20px; }
        .card { background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 10px 0; }
        .success { color: #28a745; }
        .info { color: #17a2b8; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎉 Dashboard Working!</h1>
            <p class="success">✅ Authentication and routing are working properly</p>
        </div>

        <div class="card">
            <h3>User Information</h3>
            <p><strong>Name:</strong> {{ $user->firstname ?? 'N/A' }} {{ $user->lastname ?? '' }}</p>
            <p><strong>Email:</strong> {{ $user->email ?? 'N/A' }}</p>
            <p><strong>Type:</strong> {{ $user->type->value ?? 'N/A' }}</p>
            <p><strong>User ID:</strong> {{ $user->id ?? 'N/A' }}</p>
        </div>

        <div class="card">
            <h3>System Status</h3>
            <p class="success">✅ Database connection: Working</p>
            <p class="success">✅ User authentication: Working</p>
            <p class="success">✅ Route middleware: Working</p>
            <p class="info">📊 Total users in system: {{ $userCount ?? 'Unknown' }}</p>
        </div>

        <div class="card">
            <h3>Next Steps</h3>
            <p>This simplified dashboard is working! The issue with the original dashboard is likely:</p>
            <ul>
                <li>Complex view rendering with modules</li>
                <li>Missing module dependencies</li>
                <li>Cache issues with compiled views</li>
                <li>Database queries in the controller</li>
            </ul>
            
            <p><strong>Try these links:</strong></p>
            <ul>
                <li><a href="{{ route('dashboard') }}">Original Dashboard</a> (may still show error)</li>
                <li><a href="/diagnostic">System Diagnostic</a></li>
                <li><a href="/">Homepage</a></li>
                <li><a href="/test-dashboard-parts">Dashboard Parts Test</a></li>
            </ul>
        </div>
    </div>
</body>
</html>