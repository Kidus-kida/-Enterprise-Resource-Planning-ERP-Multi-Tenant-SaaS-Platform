<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Connection Error</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f8f9fa; }
        .container { max-width: 600px; margin: 50px auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); text-align: center; }
        .error-icon { font-size: 64px; color: #dc3545; margin-bottom: 20px; }
        h1 { color: #dc3545; margin-bottom: 20px; }
        .message { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin: 20px 0; }
        .suggestions { text-align: left; background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 4px; margin: 20px 0; }
        .btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; margin: 10px; }
        .btn:hover { background: #0056b3; }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-icon">🔌</div>
        <h1>Database Connection Error</h1>
        <p>The application cannot connect to the database.</p>
        
        @if($message)
            <div class="message">
                <strong>Error Details:</strong><br>
                {{ $message }}
            </div>
        @endif

        <div class="suggestions">
            <strong>💡 What you can do:</strong>
            <ol>
                <li>Check if your database server is running</li>
                <li>Verify your database credentials in the .env file</li>
                <li>Make sure the database exists</li>
                <li>Use the diagnostic tool to test your connection</li>
            </ol>
        </div>

        <a href="/diagnostic" class="btn">🔍 Open Database Diagnostic</a>
        <a href="/" class="btn">🏠 Go to Homepage</a>
    </div>
</body>
</html>