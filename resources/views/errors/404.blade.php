<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found - {{ setting('whitelabel.browser_title', config('app.name')) }}</title>
    <style>
        body { font-family: 'Inter', Arial, sans-serif; margin: 0; padding: 20px; background: #f8f9fa; color: #333; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .container { max-width: 600px; width: 100%; bg: white; background: #fff; padding: 40px; border-radius: 8px; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15); text-align: center; }
        .logo-img { max-height: 90px; margin-bottom: 30px; object-fit: contain; }
        .error-code { font-size: 72px; font-weight: 800; color: var(--primary-color, #ff9b44); margin: 0 0 10px; }
        h1 { font-size: 24px; margin-bottom: 20px; color: #4e5e6a; }
        p { color: #888; font-size: 16px; line-height: 1.6; margin-bottom: 30px; }
        .btn { display: inline-block; padding: 12px 30px; background: var(--primary-color, #ff9b44); color: white; text-decoration: none; border-radius: 4px; font-weight: 600; transition: background 0.2s; }
        .btn:hover { background: #e07d2c; }
    </style>
</head>
<body>
    <div class="container">
        @if(setting('whitelabel.404_logo'))
            <img class="logo-img" src="{{ Storage::url(setting('whitelabel.404_logo')) }}" alt="MD Code Inc. Logo">
        @else
            <div class="brand-logo" style="font-size: 32px; font-weight: 700; color: var(--primary-color, #ff9b44); margin-bottom: 30px;">
                MD Code Inc.
            </div>
        @endif
        
        <div class="error-code">404</div>
        <h1>Page Not Found</h1>
        <p>Sorry, the page you looking for could not be found or has been moved.</p>
        
        <a href="/" class="btn">🏠 Go to Dashboard</a>
    </div>
</body>
</html>
