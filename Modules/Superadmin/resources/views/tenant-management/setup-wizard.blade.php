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
                        <li class="breadcrumb-item"><a href="{{ route('superadmin.tenant-management.index') }}">Tenant
                                Management</a></li>
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
                                <code
                                    class="text-white">{{ $business->subdomain }}.{{ config('tenancy.central_domains.0', 'ettech.et') }}</code>
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
                            <i class="fa fa-info-circle"></i> <strong>Manual Setup Required:</strong> Since you're on shared
                            hosting, you'll need to manually create the database in cPanel.
                        </div>

                        <h5>Instructions:</h5>
                        <ol>
                            <li>Log in to your <strong>cPanel</strong> account</li>
                            <li>Navigate to <strong>MySQL® Databases</strong></li>
                            <li>Create a new database with the name: <code
                                    class="bg-light p-1">{{ $setupInstructions['database_name'] }}</code></li>
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
                            <input type="text" class="form-control" value="{{ $setupInstructions['database_name'] }}"
                                readonly>
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
                                    <i class="fa fa-check-circle"></i> <strong>Database Connection Verified!</strong>
                                    Credentials are securely stored.
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

                                <button class="btn btn-secondary btn-sm"
                                    onclick="document.getElementById('verified-credentials-view').style.display='none'; document.getElementById('connection-form').style.display='block';">
                                    <i class="fa fa-pencil"></i> Edit Connection Details
                                </button>
                            </div>
                        @else
                            <p>Enter the database credentials you created in Step 1 to verify the connection:</p>
                        @endif

                        <div id="connection-form"
                            style="display: {{ isset($tenant->data['db_host']) ? 'none' : 'block' }};">
                            <form action="{{ route('superadmin.tenant-management.verify-connection', $tenant->id) }}"
                                method="POST">
                                @csrf

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Database Host <span class="text-danger">*</span></label>
                                            <input type="text" name="database_host" class="form-control"
                                                value="{{ $tenant->data['db_host'] ?? 'localhost' }}" required>
                                            <small class="text-muted">Usually 'localhost' for shared hosting</small>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Database Name <span class="text-danger">*</span></label>
                                            <input type="text" name="database_name" class="form-control"
                                                value="{{ $tenant->data['db_name'] ?? $setupInstructions['database_name'] }}"
                                                required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Database Port <span class="text-danger">*</span></label>
                                            <input type="text" name="database_port" class="form-control"
                                                value="{{ $tenant->data['db_port'] ?? '3306' }}" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Database Username <span class="text-danger">*</span></label>
                                            <input type="text" name="database_username" class="form-control"
                                                value="{{ $tenant->data['db_username'] ?? '' }}" required>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Database Password</label>
                                            <input type="password" name="database_password" class="form-control"
                                                placeholder="{{ isset($tenant->data['db_password']) ? '(Leave blank to keep existing)' : '' }}">
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-plug"></i> Verify Connection
                                </button>

                                @if(isset($tenant->data['db_host']))
                                    <button type="button" class="btn btn-link text-muted"
                                        onclick="document.getElementById('verified-credentials-view').style.display='block'; document.getElementById('connection-form').style.display='none';">
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
                                    <i class="fa fa-check-circle"></i> <strong>Tenant is Active!</strong> Database migrations have
                                    been completed successfully.
                                    <hr>
                                    <small>If you need to re-run migrations due to an error (see debugger below), you can do so
                                        below. <strong>Warning: This may overwrite existing data.</strong></small>
                                </div>
                            @else
                                <p>Once the database connection is verified, click the button below to run migrations and initialize
                                    the tenant database:</p>

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

                            <form id="migration-form"
                                action="{{ route('superadmin.tenant-management.run-migrations', $tenant->id) }}" method="POST">
                                @csrf

                                <h5 class="mt-4"><i class="fa fa-user-plus"></i>
                                    {{ $business->is_active ? 'Re-Initialize' : 'Setup' }} Admin User</h5>

                                <div class="alert alert-info">
                                    <i class="fa fa-envelope"></i> <strong>Note:</strong>
                                    The owner will receive an email to set up their password.
                                    The details below are pre-filled from the Business Owner information.
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>First Name</label>
                                            <input type="text" name="admin_firstname" class="form-control"
                                                value="{{ $business->owner_firstname }}" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Last Name</label>
                                            <input type="text" name="admin_lastname" class="form-control"
                                                value="{{ $business->owner_lastname }}" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Admin Email</label>
                                            <input type="email" name="admin_email" class="form-control"
                                                value="{{ $business->owner_email }}" readonly>
                                        </div>
                                    </div>
                                </div>

                                <button type="button"
                                    class="btn btn-{{ $business->is_active ? 'warning' : 'success' }} btn-lg mt-3"
                                    onclick="confirmMigration()">
                                    <i class="fa fa-cogs"></i>
                                    {{ $business->is_active ? 'Re-Run Migrations & Seed' : 'Run Migrations & Setup Tenant' }}
                                </button>
                            </form>

                            @if($business->is_active && !$business->owner_activated_at)
                                <form action="{{ route('superadmin.businesses.resend-invite', $business->id) }}" method="POST"
                                    class="d-inline-block ml-2">
                                    @csrf
                                    <button type="submit" class="btn btn-warning btn-lg mt-3"
                                        title="Resend the password setup email to the owner">
                                        <i class="fa fa-envelope"></i> Resend Setup Email
                                    </button>
                                </form>

                                @if($business->owner_invite_sent_at)
                                    <small class="d-block text-muted mt-2">
                                        <i class="fa fa-clock-o"></i> Last invite sent:
                                        {{ $business->owner_invite_sent_at->diffForHumans() }}
                                    </small>
                                @endif
                            @endif

                            {{-- Quick Permission Cache Clear Button --}}
                            <form action="{{ route('superadmin.tenant-management.clear-permission-cache', $tenant->id) }}"
                                method="POST" class="d-inline-block">
                                @csrf
                                <button type="submit" class="btn btn-info btn-lg mt-3 ml-2"
                                    title="Clears all caches to fix permission issues">
                                    <i class="fa fa-refresh"></i> Clear All Caches
                                </button>
                            </form>
                            <small class="d-block text-muted mt-2">
                                <i class="fa fa-info-circle"></i> Click this after migrations to fix missing menus. The tenant
                                user should refresh their browser or log out/in after this.
                            </small>
                        @endif

                        @if(session('migration_output'))
                            <div class="mt-4">
                                <h5><i class="fa fa-terminal"></i> Human-Readable Migration Log</h5>
                                <div class="bg-dark text-white p-3 rounded"
                                    style="max-height: 300px; overflow-y: auto; font-family: monospace;">
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
                        <p>To enable subdomain access (e.g., <code>{{ $business->subdomain }}.ettech.et</code>), configure
                            the following in cPanel:</p>

                        <h5>Instructions:</h5>
                        <ol>
                            <li>Go to <strong>cPanel → Subdomains</strong></li>
                            <li>Create a new subdomain: <code>{{ $business->subdomain }}</code></li>
                            <li>Point the document root to: <code>/public_html</code> (same as main domain)</li>
                            <li>The system will automatically detect the subdomain and route to the correct tenant</li>
                        </ol>

                        <div class="alert alert-warning">
                            <i class="fa fa-exclamation-triangle"></i> <strong>Note:</strong> Subdomain setup is optional.
                            Users can still access the system using the main domain with tenant identification.
                        </div>

                        <div class="alert alert-info">
                            <h6>Access URLs:</h6>
                            <ul class="mb-0">
                                <li><strong>With Subdomain:</strong>
                                    <code>https://{{ $business->subdomain }}.ettech.et</code>
                                </li>
                                <li><strong>Without Subdomain:</strong>
                                    <code>https://ettech.et?tenant={{ $tenant->id }}</code>
                                </li>
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

    <!-- Confirmation Modal -->
    <div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog" aria-labelledby="confirmationModalLabel"
        aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmationModalLabel">Confirm Re-run Migration & Seed</h5>
                </div>
                <div class="modal-body">
                    Are you sure you want to re-run database migrations and seed data for this tenant?
                    This action may overwrite existing data.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal"
                        onclick="$('#confirmationModal').modal('hide');">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="startMigration()">Yes, Proceed</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Liquid Loader Overlay -->
    <div id="liquid-loader-overlay">
        <!-- Liquid Loader CSS -->
        <style>
            #liquid-loader-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0);
                backdrop-filter: blur(5px);
                z-index: 10000;
                display: none;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            }

            .loader-container {
                position: relative;
                width: 200px;
                height: 200px;
                filter: drop-shadow(0 20px 40px rgba(0, 0, 0, 0.3));
            }

            .circle-container {
                position: relative;
                width: 100%;
                height: 100%;
                border-radius: 50%;
                background: rgba(255, 255, 255, 0.1);
                backdrop-filter: blur(10px);
                border: 4px solid rgba(255, 255, 255, 0.3);
                overflow: hidden;
                box-shadow:
                    inset 0 0 30px rgba(255, 255, 255, 0.2),
                    0 0 30px rgba(255, 255, 255, 0.1);
            }

            .liquid {
                position: absolute;
                bottom: 0;
                left: 0;
                width: 100%;
                height: 0%;
                background: linear-gradient(180deg, #00d4ff 0%, #0099cc 100%);
                animation: fillUp 3s ease-in-out infinite;
                box-shadow: 0 0 20px rgba(0, 212, 255, 0.5);
            }

            .liquid::before {
                content: '';
                position: absolute;
                top: -10px;
                left: 0;
                width: 200%;
                height: 20px;
                background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1200 120' preserveAspectRatio='none'%3E%3Cpath d='M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,52.47V0Z' fill='%2300d4ff' opacity='.8'/%3E%3Cpath d='M0,0V15.81C13,36.92,27.64,56.86,47.69,72.05,99.41,111.27,165,111,224.58,91.58c31.15-10.15,60.09-26.07,89.67-39.8,40.92-19,84.73-46,130.83-49.67,36.26-2.85,70.9,9.42,98.6,31.56,31.77,25.39,62.32,62,103.63,73,40.44,10.79,81.35-6.69,119.13-24.28s75.16-39,116.92-43.05c59.73-5.85,113.28,22.88,168.9,38.84,30.2,8.66,59,6.17,87.09-7.5,22.43-10.89,48-26.93,60.65-49.24V0Z' fill='%230099cc' opacity='.5'/%3E%3C/svg%3E");
                background-size: 50% 100%;
                animation: wave 2s linear infinite;
            }

            .bubble {
                position: absolute;
                background: rgba(255, 255, 255, 0.4);
                border-radius: 50%;
                animation: rise 2s infinite ease-in;
                bottom: -10px;
            }

            .bubble:nth-child(1) {
                width: 10px;
                height: 10px;
                left: 20%;
                animation-duration: 2.5s;
                animation-delay: 0s;
            }

            .bubble:nth-child(2) {
                width: 15px;
                height: 15px;
                left: 50%;
                animation-duration: 2s;
                animation-delay: 0.5s;
            }

            .bubble:nth-child(3) {
                width: 8px;
                height: 8px;
                left: 70%;
                animation-duration: 2.2s;
                animation-delay: 1s;
            }

            .bubble:nth-child(4) {
                width: 12px;
                height: 12px;
                left: 35%;
                animation-duration: 1.8s;
                animation-delay: 1.5s;
            }

            .percentage {
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                color: white;
                font-size: 36px;
                font-weight: 700;
                letter-spacing: 2px;
                z-index: 10;
                text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
            }

            .loading-text {
                position: absolute;
                bottom: -60px;
                left: 50%;
                transform: translateX(-50%);
                color: rgba(255, 255, 255, 0.9);
                font-size: 14px;
                letter-spacing: 3px;
                text-transform: uppercase;
                width: 300px;
                text-align: center;
            }

            .loading-text::after {
                content: '';
                animation: dots 1.5s steps(4, end) infinite;
            }

            .glow {
                position: absolute;
                top: -10px;
                left: -10px;
                right: -10px;
                bottom: -10px;
                border-radius: 50%;
                background: radial-gradient(circle, rgba(0, 212, 255, 0.3) 0%, transparent 70%);
                animation: pulse 2s ease-in-out infinite;
                z-index: -1;
            }

            @keyframes fillUp {
                0% {
                    height: 0%;
                }

                100% {
                    height: 100%;
                    transition: height 1s;
                }
            }

            /* Override the infinite bounce to be controlled by JS for progress value */
            /* We will remove the animation property in JS when we start progress manually */

            @keyframes wave {
                0% {
                    transform: translateX(0);
                }

                100% {
                    transform: translateX(-50%);
                }
            }

            @keyframes rise {
                0% {
                    bottom: -10px;
                    opacity: 0;
                    transform: translateX(0);
                }

                50% {
                    opacity: 1;
                }

                100% {
                    bottom: 100%;
                    opacity: 0;
                    transform: translateX(10px);
                }
            }

            @keyframes pulse {

                0%,
                100% {
                    transform: scale(1);
                    opacity: 0.5;
                }

                50% {
                    transform: scale(1.1);
                    opacity: 0.8;
                }
            }

            @keyframes dots {
                0% {
                    content: '';
                }

                25% {
                    content: '.';
                }

                50% {
                    content: '..';
                }

                75% {
                    content: '...';
                }

                100% {
                    content: '';
                }
            }
        </style>

        <div class="loader-container">
            <div class="glow"></div>
            <div class="circle-container">
                <div class="liquid" id="loader-liquid" style="animation: none; height: 0%;">
                    <div class="bubble"></div>
                    <div class="bubble"></div>
                    <div class="bubble"></div>
                    <div class="bubble"></div>
                </div>
                <div class="percentage" id="loader-percentage">0%</div>
            </div>
            <div class="loading-text">Initializing Tenant Database</div>
        </div>
    </div>


    <script>
        function confirmMigration() {
            $('#confirmationModal').modal('show');
        }

        // Warning function for beforeunload event
        function onBeforeUnload(e) {
            const message = 'A critical tenant database operation is currently in progress. Refreshing or closing this page may cause data inconsistency. Are you sure you want to leave?';
            e.returnValue = message;
            return message;
        }

        function startMigration() {
            // Close modal
            $('#confirmationModal').modal('hide');

            // Show loader
            const overlay = document.getElementById('liquid-loader-overlay');
            const liquid = document.getElementById('loader-liquid');
            const percentage = document.getElementById('loader-percentage');

            overlay.style.display = 'flex';

            // Reset state
            liquid.style.height = '0%';
            percentage.textContent = '0%';

            // Activate safety warning
            window.addEventListener('beforeunload', onBeforeUnload);

            // Start progress simulation
            let progress = 0;
            const interval = setInterval(() => {
                if (progress < 90) {
                    // Slow down as it gets higher
                    const increment = progress < 50 ? 2 : (progress < 80 ? 1 : 0.5);
                    progress += increment;
                    progress = Math.min(progress, 90); // Cap at 90 until real success

                    liquid.style.height = progress + '%';
                    percentage.textContent = Math.floor(progress) + '%';
                }
            }, 100);

            // Submit form via AJAX
            const form = document.getElementById('migration-form');
            const formData = new FormData(form);

            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
                .then(response => response.json())
                .then(data => {
                    clearInterval(interval);
                    // Remove safety warning
                    window.removeEventListener('beforeunload', onBeforeUnload);

                    if (data.success) {
                        // Complete the progress to 100%
                        liquid.style.height = '100%';
                        percentage.textContent = '100%';

                        // Slight delay to let user see 100%
                        setTimeout(() => {
                            alert(data.message || 'Tenant database migration and seeding completed successfully.');
                            window.location.reload();
                        }, 500);
                    } else {
                        // Error handling
                        overlay.style.display = 'none';
                        alert(data.message || 'An error occurred during migration.');
                    }
                })
                .catch(error => {
                    clearInterval(interval);
                    // Remove safety warning
                    window.removeEventListener('beforeunload', onBeforeUnload);

                    overlay.style.display = 'none';
                    console.error('Error:', error);
                    alert('An unexpected error occurred. Please check the logs and try again.');
                });
        }
    </script>

@endsection