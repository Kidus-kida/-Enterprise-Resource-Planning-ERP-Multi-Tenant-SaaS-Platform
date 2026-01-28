<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laravel Database Diagnostic</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .status-success { color: #28a745; }
        .status-error { color: #dc3545; }
        .status-warning { color: #ffc107; }
        .card { border: 1px solid #ddd; border-radius: 8px; padding: 15px; margin: 10px 0; }
        .card h3 { margin-top: 0; }
        .btn { padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; margin: 5px; }
        .btn-primary { background: #007bff; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-warning { background: #ffc107; color: black; }
        .form-group { margin: 10px 0; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        @media (max-width: 768px) { .grid { grid-template-columns: 1fr; } }
        .log { background: #f8f9fa; padding: 10px; border-radius: 4px; font-family: monospace; white-space: pre-wrap; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Laravel Database Diagnostic</h1>
        
        <div class="grid">
            <!-- Environment Validation -->
            <div class="card">
                <h3>Environment Configuration</h3>
                
                @if($envValidation['valid'])
                    <p class="status-success">✅ Environment configuration is valid</p>
                @else
                    <p class="status-error">❌ Environment configuration has issues</p>
                    
                    @if(!empty($envValidation['errors']))
                        <h4>Errors:</h4>
                        <ul>
                            @foreach($envValidation['errors'] as $error)
                                <li class="status-error">{{ $error }}</li>
                            @endforeach
                        </ul>
                    @endif
                @endif

                @if(!empty($envValidation['warnings']))
                    <h4>Warnings:</h4>
                    <ul>
                        @foreach($envValidation['warnings'] as $warning)
                            <li class="status-warning">{{ $warning }}</li>
                        @endforeach
                    </ul>
                @endif

                @if(!empty($envValidation['suggestions']))
                    <h4>Suggestions:</h4>
                    <ul>
                        @foreach($envValidation['suggestions'] as $suggestion)
                            <li>{{ $suggestion }}</li>
                        @endforeach
                    </ul>
                @endif

                <h4>Current Database Configuration:</h4>
                <div class="log">
@foreach($envVars as $key => $value)
{{ $key }}: {{ $value }}
@endforeach
                </div>

                <button class="btn btn-primary" onclick="fixConfig()">🔧 Fix Configuration Issues</button>
            </div>

            <!-- Database Connection Test -->
            <div class="card">
                <h3>Database Connection Test</h3>
                
                @if($dbTest['success'])
                    <p class="status-success">✅ {{ $dbTest['message'] }}</p>
                    <p>Connected to: {{ $dbTest['database'] }} on {{ $dbTest['host'] }}</p>
                @else
                    <p class="status-error">❌ {{ $dbTest['message'] }}</p>
                    
                    @if(!empty($alternativeTests))
                        <h4>Alternative Configuration Tests:</h4>
                        @foreach($alternativeTests as $name => $test)
                            @if(isset($test['skipped']))
                                <p class="status-warning">⚠️ {{ $name }}: {{ $test['message'] }}</p>
                            @elseif($test['success'])
                                <p class="status-success">✅ {{ $name }}: {{ $test['message'] }}</p>
                                <div class="log">
Working configuration:
Host: {{ $test['host'] }}
Port: {{ $test['port'] }}
Database: {{ $test['database'] }}
Username: {{ $test['username'] }}
                                </div>
                            @else
                                <p class="status-error">❌ {{ $name }}: {{ $test['message'] }}</p>
                            @endif
                        @endforeach
                    @endif
                @endif

                <button class="btn btn-success" onclick="testCurrentConnection()">🔄 Test Current Connection</button>
            </div>
        </div>

        <!-- Migration Status -->
        <div class="card">
            <h3>Database Migration Status</h3>
            
            @if(isset($migrationStatus))
                @if($migrationStatus['migrations_table_exists'])
                    <p class="status-success">✅ Migrations table exists ({{ $migrationStatus['run_migrations'] }} migrations run)</p>
                @else
                    <p class="status-error">❌ Migrations table does not exist</p>
                @endif

                @if($migrationStatus['users_table_exists'])
                    <p class="status-success">✅ Users table exists</p>
                @else
                    <p class="status-error">❌ Users table does not exist</p>
                @endif

                @if($migrationStatus['needs_migration'])
                    <div class="log">
⚠️ Database needs migration!
This is likely the cause of your HTTP 500 errors.
                    </div>
                    <button class="btn btn-warning" onclick="runMigrations()">🚀 Run Database Migrations</button>
                @else
                    <p class="status-success">✅ Database appears to be properly migrated</p>
                @endif
            @endif
        </div>

        <!-- Table Status -->
        @if(isset($tableStatus))
        <div class="card">
            <h3>Essential Tables Status</h3>
            @foreach($tableStatus as $table => $status)
                <div style="margin: 10px 0;">
                    @if($status['exists'])
                        <p class="status-success">✅ {{ $table }}: {{ $status['count'] }} records</p>
                    @else
                        <p class="status-error">❌ {{ $table }}: Missing</p>
                    @endif
                    <small>{{ $status['description'] }}</small>
                </div>
            @endforeach
        </div>
        @endif

        <!-- Custom Connection Test -->
        <div class="card">
            <h3>Test Custom Database Configuration</h3>
            <div class="grid">
                <div>
                    <div class="form-group">
                        <label>Host:</label>
                        <input type="text" id="test-host" value="127.0.0.1">
                    </div>
                    <div class="form-group">
                        <label>Port:</label>
                        <input type="text" id="test-port" value="3306">
                    </div>
                    <div class="form-group">
                        <label>Database:</label>
                        <input type="text" id="test-database" value="{{ $envVars['DB_DATABASE'] ?? '' }}">
                    </div>
                </div>
                <div>
                    <div class="form-group">
                        <label>Username:</label>
                        <input type="text" id="test-username" value="root">
                    </div>
                    <div class="form-group">
                        <label>Password:</label>
                        <input type="password" id="test-password" value="">
                    </div>
                    <div class="form-group">
                        <button class="btn btn-primary" onclick="testCustomConnection()">🧪 Test Connection</button>
                    </div>
                </div>
            </div>
            <div id="test-results" class="log" style="display: none;"></div>
        </div>

        <!-- Quick Fixes -->
        <div class="card">
            <h3>Quick Fixes</h3>
            <p>Try these common solutions:</p>
            <button class="btn btn-warning" onclick="clearCache()">🗑️ Clear Config Cache</button>
            <button class="btn btn-primary" onclick="window.location.reload()">🔄 Refresh Page</button>
            
            <h4>Manual Steps:</h4>
            <ol>
                <li>Check if MySQL server is running</li>
                <li>Verify database exists: <code>{{ $envVars['DB_DATABASE'] ?? 'not set' }}</code></li>
                <li>Test MySQL connection manually</li>
                <li>Update .env file with correct credentials</li>
                <li>Run: <code>php artisan config:clear</code></li>
            </ol>
        </div>
    </div>

    <script>
        function fixConfig() {
            fetch('/diagnostic/fix-config', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
                .then(response => response.json())
                .then(data => {
                    alert(data.results.join('\n'));
                    window.location.reload();
                });
        }

        function testCurrentConnection() {
            window.location.reload();
        }

        function testCustomConnection() {
            const data = {
                host: document.getElementById('test-host').value,
                port: document.getElementById('test-port').value,
                database: document.getElementById('test-database').value,
                username: document.getElementById('test-username').value,
                password: document.getElementById('test-password').value,
            };

            fetch('/diagnostic/test-connection', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                const results = document.getElementById('test-results');
                results.style.display = 'block';
                
                let output = data.result.success ? '✅ ' : '❌ ';
                output += data.result.message + '\n\n';
                
                if (data.result.success) {
                    output += 'Connection Details:\n';
                    output += `Host: ${data.result.host}\n`;
                    output += `Port: ${data.result.port}\n`;
                    output += `Database: ${data.result.database}\n`;
                    output += `Username: ${data.result.username}\n\n`;
                    output += '💡 This configuration works! Update your .env file:\n';
                    output += `DB_HOST=${data.result.host}\n`;
                    output += `DB_PORT=${data.result.port}\n`;
                    output += `DB_DATABASE=${data.result.database}\n`;
                    output += `DB_USERNAME=${data.result.username}\n`;
                    output += `DB_PASSWORD=${data.password || ''}\n`;
                } else {
                    output += 'Suggestions:\n';
                    data.suggestions.forEach(suggestion => {
                        output += `• ${suggestion}\n`;
                    });
                }
                
                results.textContent = output;
            });
        }

        function clearCache() {
            fetch('/diagnostic/fix-config', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
                .then(response => response.json())
                .then(data => {
                    alert('Cache cleared! Refresh the page to see updated results.');
                });
        }

        function runMigrations() {
            if (!confirm('This will run database migrations. Are you sure?')) {
                return;
            }

            const button = event.target;
            button.disabled = true;
            button.textContent = '🔄 Running Migrations...';

            fetch('/diagnostic/run-migrations', { 
                method: 'POST', 
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } 
            })
            .then(response => response.json())
            .then(data => {
                let message = 'Migration Results:\n\n';
                message += 'Migration: ' + (data.migration_result.success ? '✅ Success' : '❌ Failed') + '\n';
                message += data.migration_result.message + '\n\n';
                
                if (data.user_result) {
                    message += 'Test User: ' + (data.user_result.success ? '✅ Success' : '❌ Failed') + '\n';
                    message += data.user_result.message + '\n';
                }

                alert(message);
                
                if (data.success) {
                    window.location.reload();
                }
            })
            .catch(error => {
                alert('Error running migrations: ' + error.message);
            })
            .finally(() => {
                button.disabled = false;
                button.textContent = '🚀 Run Database Migrations';
            });
        }
    </script>
</body>
</html>