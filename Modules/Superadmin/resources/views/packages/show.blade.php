@extends('layouts.app')

@section('page-content')
    <div class="content container-fluid">
        
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Package Details</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('superadmin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('superadmin.packages.index') }}">Packages</a></li>
                        <li class="breadcrumb-item active">{{ $package->name }}</li>
                    </ul>
                </div>
                <div class="col-auto float-end ms-auto">
                    <a href="{{ route('superadmin.packages.edit', $package->id) }}" class="btn btn-primary">
                        <i class="fa fa-pencil"></i> Edit Package
                    </a>
                </div>
            </div>
        </div>

        <!-- Package Details -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <!-- Left Column -->
                            <div class="col-md-6">
                                <h4 class="card-title">Package Information</h4>
                                
                                <table class="table table-borderless">
                                    <tbody>
                                        <tr>
                                            <th width="40%">Package Name:</th>
                                            <td>{{ $package->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>Description:</th>
                                            <td>{{ $package->description ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Price:</th>
                                            <td><strong class="text-success">{{ number_format($package->price, 2) }} ETB</strong></td>
                                        </tr>
                                        <tr>
                                            <th>Billing Cycle:</th>
                                            <td>{{ $package->interval_count }} {{ ucfirst($package->interval) }}</td>
                                        </tr>
                                        <tr>
                                            <th>Trial Period:</th>
                                            <td>{{ $package->trial_days }} days</td>
                                        </tr>
                                        <tr>
                                            <th>Status:</th>
                                            <td>
                                                @if($package->is_active)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-danger">Inactive</span>
                                                @endif
                                                
                                                @if($package->is_private)
                                                    <span class="badge bg-warning">Private</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Sort Order:</th>
                                            <td>{{ $package->sort_order }}</td>
                                        </tr>
                                        <tr>
                                            <th>Created:</th>
                                            <td>{{ $package->created_at->format('d M Y, h:i A') }}</td>
                                        </tr>
                                        <tr>
                                            <th>Last Updated:</th>
                                            <td>{{ $package->updated_at->format('d M Y, h:i A') }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Right Column -->
                            <div class="col-md-6">
                                <h4 class="card-title">Limits & Features</h4>
                                
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h5>Usage Limits</h5>
                                        <ul class="list-unstyled">
                                            <li class="mb-2">
                                                <i class="fa fa-building text-primary"></i> 
                                                <strong>Locations:</strong> 
                                                {{ $package->location_count == 0 ? 'Unlimited' : $package->location_count }}
                                            </li>
                                            <li class="mb-2">
                                                <i class="fa fa-users text-primary"></i> 
                                                <strong>Users:</strong> 
                                                {{ $package->user_count == 0 ? 'Unlimited' : $package->user_count }}
                                            </li>
                                            <li class="mb-2">
                                                <i class="fa fa-cube text-primary"></i> 
                                                <strong>Products:</strong> 
                                                {{ $package->product_count == 0 ? 'Unlimited' : $package->product_count }}
                                            </li>
                                            <li class="mb-2">
                                                <i class="fa fa-file-text text-primary"></i> 
                                                <strong>Invoices:</strong> 
                                                {{ $package->invoice_count == 0 ? 'Unlimited' : $package->invoice_count }}
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="card bg-light mt-3">
                                    <div class="card-body">
                                        <h5>Module Permissions</h5>
                                        @php
                                            $permissions = $package->custom_permissions ?? [];
                                        @endphp
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <ul class="list-unstyled">
                                                    <li class="mb-2">
                                                        @if(isset($permissions['contacts']) && $permissions['contacts'])
                                                            <i class="fa fa-check-circle text-success"></i>
                                                        @else
                                                            <i class="fa fa-times-circle text-danger"></i>
                                                        @endif
                                                        Contacts
                                                    </li>
                                                    <li class="mb-2">
                                                        @if(isset($permissions['products']) && $permissions['products'])
                                                            <i class="fa fa-check-circle text-success"></i>
                                                        @else
                                                            <i class="fa fa-times-circle text-danger"></i>
                                                        @endif
                                                        Products
                                                    </li>
                                                    <li class="mb-2">
                                                        @if(isset($permissions['pos']) && $permissions['pos'])
                                                            <i class="fa fa-check-circle text-success"></i>
                                                        @else
                                                            <i class="fa fa-times-circle text-danger"></i>
                                                        @endif
                                                        POS
                                                    </li>
                                                    <li class="mb-2">
                                                        @if(isset($permissions['purchases']) && $permissions['purchases'])
                                                            <i class="fa fa-check-circle text-success"></i>
                                                        @else
                                                            <i class="fa fa-times-circle text-danger"></i>
                                                        @endif
                                                        Purchases
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="col-md-6">
                                                <ul class="list-unstyled">
                                                    <li class="mb-2">
                                                        @if(isset($permissions['accounting']) && $permissions['accounting'])
                                                            <i class="fa fa-check-circle text-success"></i>
                                                        @else
                                                            <i class="fa fa-times-circle text-danger"></i>
                                                        @endif
                                                        Accounting
                                                    </li>
                                                    <li class="mb-2">
                                                        @if(isset($permissions['reports']) && $permissions['reports'])
                                                            <i class="fa fa-check-circle text-success"></i>
                                                        @else
                                                            <i class="fa fa-times-circle text-danger"></i>
                                                        @endif
                                                        Reports
                                                    </li>
                                                    <li class="mb-2">
                                                        @if(isset($permissions['hr']) && $permissions['hr'])
                                                            <i class="fa fa-check-circle text-success"></i>
                                                        @else
                                                            <i class="fa fa-times-circle text-danger"></i>
                                                        @endif
                                                        HR Management
                                                    </li>
                                                    <li class="mb-2">
                                                        @if(isset($permissions['payroll']) && $permissions['payroll'])
                                                            <i class="fa fa-check-circle text-success"></i>
                                                        @else
                                                            <i class="fa fa-times-circle text-danger"></i>
                                                        @endif
                                                        Payroll
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Subscriptions Using This Package -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Subscriptions ({{ $package->subscriptions->count() }})</h4>
                    </div>
                    <div class="card-body">
                        @if($package->subscriptions->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Business</th>
                                            <th>Start Date</th>
                                            <th>End Date</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($package->subscriptions as $subscription)
                                        <tr>
                                            <td>{{ $subscription->business->name ?? 'N/A' }}</td>
                                            <td>{{ $subscription->start_date ? $subscription->start_date->format('d M Y') : 'N/A' }}</td>
                                            <td>{{ $subscription->end_date ? $subscription->end_date->format('d M Y') : 'N/A' }}</td>
                                            <td>
                                                @if($subscription->status == 'approved')
                                                    <span class="badge bg-success">Approved</span>
                                                @elseif($subscription->status == 'waiting')
                                                    <span class="badge bg-warning">Waiting</span>
                                                @else
                                                    <span class="badge bg-danger">Declined</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('superadmin.subscriptions.show', $subscription->id) }}" class="btn btn-sm btn-primary">
                                                    View
                                                </a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted">No subscriptions using this package yet.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
