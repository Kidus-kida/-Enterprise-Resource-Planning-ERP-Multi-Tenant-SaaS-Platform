<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server Error</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f8f9fa; }
        .container { max-width: 600px; margin: 50px auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); text-align: center; }
        .error-icon { font-size: 64px; color: #dc3545; margin-bottom: 20px; }
        h1 { color: #dc3545; margin-bottom: 20px; }
        .message { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin: 20px 0; }
        .suggestions { text-align: left; background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 4px; margin: 20px 0; }
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
        <h1>Server Error (500)</h1>
        <p>Something went wrong on our end.</p>
        
        @if(isset($message))
            <div class="message">
                {{ $message }}
            </div>
        @endif

        <div class="suggestions">
            <strong>💡 What you can do:</strong>
            <ol>
                <li>Try refreshing the page</li>
                <li>Check if you're logged in properly</li>
                <li>Use the diagnostic tool to check system status</li>
                <li>Contact support if the problem persists</li>
            </ol>
        </div>

        <a href="/" class="btn">🏠 Go to Homepage</a>
        <a href="/diagnostic" class="btn btn-secondary">🔍 System Diagnostic</a>
        <a href="javascript:history.back()" class="btn btn-secondary">← Go Back</a>
    </div>
</body>
</html>