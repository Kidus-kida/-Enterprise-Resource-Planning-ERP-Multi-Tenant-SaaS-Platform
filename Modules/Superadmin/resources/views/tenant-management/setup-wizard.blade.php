@extends('layouts.app')

@section('page-content')
    <div class="content container-fluid">
        
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Tenant Setup Wizard</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('superadmin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('superadmin.tenant-management.index') }}">Tenant Management</a></li>
                        <li class="breadcrumb-item active">Setup Wizard</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Alerts -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fa fa-check-circle"></i> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fa fa-times-circle"></i> {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fa fa-exclamation-circle"></i> <strong>Please check the following errors:</strong>
                <ul class="mb-0 mt-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <!-- Business Info -->
        <div class="row">
            <div class="col-md-12">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h4 class="text-white mb-3">
                            <i class="fa fa-building"></i> {{ $business->name }}
                        </h4>
                        <div class="row">
                            <div class="col-md-3">
                                <strong>Subdomain:</strong><br>
                                <code class="text-white">{{ $business->subdomain }}.{{ config('tenancy.central_domains.0', 'tewoshr.com') }}</code>
                            </div>
                            <div class="col-md-3">
                                <strong>Package:</strong><br>
                                {{ $business->subscription->package->name ?? 'N/A' }}
                            </div>
                            <div class="col-md-3">
                                <strong>Status:</strong><br>
                                @if($business->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-warning">Pending Setup</span>
                                @endif
                            </div>
                            <div class="col-md-3">
                                <strong>Tenant ID:</strong><br>
                                <code class="text-white">{{ $tenant->id }}</code>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Setup Steps -->
        <div class="row">
            <!-- Step 1: cPanel Instructions -->
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h4 class="card-title mb-0 text-white">
                            <i class="fa fa-server"></i> Step 1: Create Database in cPanel
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fa fa-info-circle"></i> <strong>Manual Setup Required:</strong> Since you're on shared hosting, you'll need to manually create the database in cPanel.
                        </div>

                        <h5>Instructions:</h5>
                        <ol>
                            <li>Log in to your <strong>cPanel</strong> account</li>
                            <li>Navigate to <strong>MySQL® Databases</strong></li>
                            <li>Create a new database with the name: <code class="bg-light p-1">{{ $setupInstructions['database_name'] }}</code></li>
                            <li>Create a new MySQL user or use an existing one</li>
                            <li>Add the user to the database with <strong>ALL PRIVILEGES</strong></li>
                            <li>Note down the following information for Step 2:
                                <ul>
                                    <li>Database Name</li>
                                    <li>Database Username</li>
                                    <li>Database Password</li>
                                    <li>Database Host (usually: <code>localhost</code>)</li>
                                </ul>
                            </li>
                        </ol>

                        <div class="alert alert-success mt-3">
                            <h6><i class="fa fa-lightbulb-o"></i> Recommended Database Name:</h6>
                            <input type="text" class="form-control" value="{{ $setupInstructions['database_name'] }}" readonly>
                            <small class="text-muted">You can use this suggested name or choose your own</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 2: Verify Database Connection -->
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-warning">
                        <h4 class="card-title mb-0">
                            <i class="fa fa-plug"></i> Step 2: Verify Database Connection
                        </h4>
                    </div>
                    <div class="card-body">
                        @if(isset($tenant->data['db_host']))
                            <div id="verified-credentials-view">
                                <div class="alert alert-success">
                                    <i class="fa fa-check-circle"></i> <strong>Database Connection Verified!</strong> Credentials are securely stored.
                                </div>
                                
                                @php
                                    $credentials = $tenant->data ?? [];
                                @endphp
                                
                                <table class="table table-bordered">
                                    <tr>
                                        <th>Database Host:</th>
                                        <td><code>{{ $credentials['db_host'] ?? 'N/A' }}</code></td>
                                    </tr>
                                    <tr>
                                        <th>Database Port:</th>
                                        <td><code>{{ $credentials['db_port'] ?? '3306' }}</code></td>
                                    </tr>
                                    <tr>
                                        <th>Database Name:</th>
                                        <td><code>{{ $credentials['db_name'] ?? 'N/A' }}</code></td>
                                    </tr>
                                    <tr>
                                        <th>Database Username:</th>
                                        <td><code>{{ $credentials['db_username'] ?? 'N/A' }}</code></td>
                                    </tr>
                                    <tr>
                                        <th>Password:</th>
                                        <td><code>••••••••</code> (encrypted)</td>
                                    </tr>
                                </table>

                                <button class="btn btn-secondary btn-sm" onclick="document.getElementById('verified-credentials-view').style.display='none'; document.getElementById('connection-form').style.display='block';">
                                    <i class="fa fa-pencil"></i> Edit Connection Details
                                </button>
                            </div>
                        @else
                            <p>Enter the database credentials you created in Step 1 to verify the connection:</p>
                        @endif
                            
                            <div id="connection-form" style="display: {{ isset($tenant->data['db_host']) ? 'none' : 'block' }};">
                                <form action="{{ route('superadmin.tenant-management.verify-connection', $tenant->id) }}" method="POST">
                                    @csrf
                                    
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Database Host <span class="text-danger">*</span></label>
                                                <input type="text" name="database_host" class="form-control" value="{{ $tenant->data['db_host'] ?? 'localhost' }}" required>
                                                <small class="text-muted">Usually 'localhost' for shared hosting</small>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Database Name <span class="text-danger">*</span></label>
                                                <input type="text" name="database_name" class="form-control" value="{{ $tenant->data['db_name'] ?? $setupInstructions['database_name'] }}" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Database Port <span class="text-danger">*</span></label>
                                                <input type="text" name="database_port" class="form-control" value="{{ $tenant->data['db_port'] ?? '3306' }}" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Database Username <span class="text-danger">*</span></label>
                                                <input type="text" name="database_username" class="form-control" value="{{ $tenant->data['db_username'] ?? '' }}" required>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Database Password</label>
                                                <input type="password" name="database_password" class="form-control" placeholder="{{ isset($tenant->data['db_password']) ? '(Leave blank to keep existing)' : '' }}">
                                            </div>
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-primary">
                                        <i class="fa fa-plug"></i> Verify Connection
                                    </button>

                                    @if(isset($tenant->data['db_host']))
                                        <button type="button" class="btn btn-link text-muted" onclick="document.getElementById('verified-credentials-view').style.display='block'; document.getElementById('connection-form').style.display='none';">
                                            Cancel
                                        </button>
                                    @endif
                                </form>
                            </div>
                    </div>
                </div>
            </div>

            <!-- Step 3: Run Migrations -->
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h4 class="card-title mb-0 text-white">
                            <i class="fa fa-cogs"></i> Step 3: Initialize Tenant Database
                        </h4>
                    </div>
                    <div class="card-body">
                        @if(!isset($tenant->data['db_host']))
                            <div class="alert alert-warning">
                                <i class="fa fa-exclamation-triangle"></i> Please complete Step 2 first!
                            </div>
                        @else
                            @if($business->is_active)
                                <div class="alert alert-success">
                                    <i class="fa fa-check-circle"></i> <strong>Tenant is Active!</strong> Database migrations have been completed successfully.
                                    <hr>
                                    <small>If you need to re-run migrations due to an error (see debugger below), you can do so below. <strong>Warning: This may overwrite existing data.</strong></small>
                                </div>
                            @else
                                <p>Once the database connection is verified, click the button below to run migrations and initialize the tenant database:</p>
                                
                                <div class="alert alert-info">
                                    <h6><i class="fa fa-info-circle"></i> What will happen:</h6>
                                    <ul class="mb-0">
                                        <li>All database tables will be created in the tenant database</li>
                                        <li>Default data will be seeded</li>
                                        <li>The business will be marked as <strong>Active</strong></li>
                                        <li>Users can start using the system</li>
                                    </ul>
                                </div>
                            @endif

                            <form action="{{ route('superadmin.tenant-management.run-migrations', $tenant->id) }}" method="POST">
                                @csrf
                                
                                <h5 class="mt-4"><i class="fa fa-user-plus"></i> {{ $business->is_active ? 'Re-Initialize' : 'Setup' }} Admin User</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>First Name <span class="text-danger">*</span></label>
                                            <input type="text" name="admin_firstname" class="form-control" value="{{ $business->owner->firstname ?? '' }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Last Name <span class="text-danger">*</span></label>
                                            <input type="text" name="admin_lastname" class="form-control" value="{{ $business->owner->lastname ?? '' }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Admin Email <span class="text-danger">*</span></label>
                                            <input type="email" name="admin_email" class="form-control" value="{{ $business->owner->email ?? '' }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Admin Password <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input type="password" name="admin_password" id="admin_password" class="form-control" required minlength="8">
                                                <div class="input-group-append">
                                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword" title="Show/Hide Password">
                                                        <i class="fa fa-eye" id="togglePasswordIcon"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <small class="text-muted">Minimum 8 characters</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Admin Username <span class="text-danger">*</span></label>
                                    <input type="text" name="admin_username" class="form-control" value="{{ explode('@', $business->owner->email ?? '')[0] }}" required>
                                </div>

                                <button type="submit" class="btn btn-{{ $business->is_active ? 'warning' : 'success' }} btn-lg mt-3" onclick="return confirm('Are you sure you want to run migrations and seed the database? This might take a few moments.');">
                                    <i class="fa fa-cogs"></i> {{ $business->is_active ? 'Re-Run Migrations & Seed' : 'Run Migrations & Setup Tenant' }}
                                </button>
                            </form>
                        @endif

                        @if(session('migration_output'))
                            <div class="mt-4">
                                <h5><i class="fa fa-terminal"></i> Human-Readable Migration Log</h5>
                                <div class="bg-dark text-white p-3 rounded" style="max-height: 300px; overflow-y: auto; font-family: monospace;">
                                    <pre class="text-white mb-0">{{ session('migration_output') }}</pre>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Step 4: Subdomain Setup -->
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="card-title mb-0 text-white">
                            <i class="fa fa-globe"></i> Step 4: Configure Subdomain (Optional)
                        </h4>
                    </div>
                    <div class="card-body">
                        <p>To enable subdomain access (e.g., <code>{{ $business->subdomain }}.tewoshr.com</code>), configure the following in cPanel:</p>
                        
                        <h5>Instructions:</h5>
                        <ol>
                            <li>Go to <strong>cPanel → Subdomains</strong></li>
                            <li>Create a new subdomain: <code>{{ $business->subdomain }}</code></li>
                            <li>Point the document root to: <code>/public_html</code> (same as main domain)</li>
                            <li>The system will automatically detect the subdomain and route to the correct tenant</li>
                        </ol>

                        <div class="alert alert-warning">
                            <i class="fa fa-exclamation-triangle"></i> <strong>Note:</strong> Subdomain setup is optional. Users can still access the system using the main domain with tenant identification.
                        </div>

                        <div class="alert alert-info">
                            <h6>Access URLs:</h6>
                            <ul class="mb-0">
                                <li><strong>With Subdomain:</strong> <code>https://{{ $business->subdomain }}.tewoshr.com</code></li>
                                <li><strong>Without Subdomain:</strong> <code>https://tewoshr.com?tenant={{ $tenant->id }}</code></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="row mt-3">
            <div class="col-md-12">
                <a href="{{ route('superadmin.tenant-management.index') }}" class="btn btn-secondary">
                    <i class="fa fa-arrow-left"></i> Back to Tenant List
                </a>
                <a href="{{ route('superadmin.businesses.show', $business->id) }}" class="btn btn-info">
                    <i class="fa fa-building"></i> View Business Details
                </a>
            </div>
        </div>

    </div>


<script>
// Password visibility toggle
document.addEventListener('DOMContentLoaded', function() {
    const togglePassword = document.getElementById('togglePassword');
    const passwordField = document.getElementById('admin_password');
    const toggleIcon = document.getElementById('togglePasswordIcon');
    
    if (togglePassword && passwordField && toggleIcon) {
        togglePassword.addEventListener('click', function() {
            // Toggle password visibility
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
            
            // Toggle icon
            if (type === 'password') {
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            } else {
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            }
        });
    }
});
</script>

@endsection
