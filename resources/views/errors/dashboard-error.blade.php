<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Error</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f8f9fa; }
        .container { max-width: 600px; margin: 50px auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); text-align: center; }
        .error-icon { font-size: 64px; color: #ffc107; margin-bottom: 20px; }
        h1 { color: #ffc107; margin-bottom: 20px; }
        .message { background: #fff3cd; color: #856404; padding: 15px; border-radius: 4px; margin: 20px 0; }
        .error-details { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin: 20px 0; font-family: monospace; text-align: left; }
        .btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; margin: 10px; }
        .btn:hover { background: #0056b3; }
        .btn-secondary { background: #6c757d; }
        .btn-secondary:hover { background: #545b62; }
    </style>
</head>
<body>
    <div class="container">
        <div class="brand-logo" style="font-size: 28px; font-weight: 700; color: #333; margin-bottom: 20px;">
            MD Code Inc.
        </div>
        <div class="error-icon">⚠️</div>
        <h1>Dashboard Temporarily Unavailable</h1>
        
        <div class="message">
            {{ $message ?? 'The dashboard is experiencing technical difficulties.' }}
        </div>

        @if($error && config('app.debug'))
            <div class="error-details">
                <strong>Debug Information:</strong><br>
                {{ $error }}
            </div>
        @endif

        <p>The system is working, but some dashboard features may be temporarily unavailable.</p>

        <a href="/dashboard-simple" class="btn">📊 Simple Dashboard</a>
        <a href="/diagnostic" class="btn btn-secondary">🔍 System Diagnostic</a>
        <a href="/" class="btn btn-secondary">🏠 Homepage</a>
    </div>
</body>
</html>