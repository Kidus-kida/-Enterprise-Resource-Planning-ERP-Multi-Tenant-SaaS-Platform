@extends('layouts.app', ['pageTitle' => 'Tenant Dashboard'])

@section('page-content')
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Tenant Dashboard</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Tenant Workspace</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-8">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <h4 class="mb-2">Welcome to the tenant workspace.</h4>
                        <p class="text-muted mb-4">This tenant workspace is now rendering inside the main ERP shell with business and module context.</p>

                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="border rounded p-4 h-100">
                                    <h5 class="mb-3">Tenant details</h5>
                                    <p class="mb-2"><strong>Tenant:</strong> {{ $tenantSlug }}</p>
                                    <p class="mb-2"><strong>Business:</strong> {{ $tenantModel?->business?->name ?? 'No linked business found' }}</p>
                                    <p class="mb-0"><strong>Status:</strong> Active</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded p-4 h-100">
                                    <h5 class="mb-3">Enabled modules</h5>
                                    @if(!empty($enabledModules))
                                        @foreach($enabledModules as $module)
                                            <span class="badge bg-primary me-2 mb-2">{{ $module }}</span>
                                        @endforeach
                                    @else
                                        <p class="text-muted mb-0">No enabled modules were found for this tenant yet.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <h5 class="mb-3">Quick actions</h5>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2"><a href="#">Open company profile</a></li>
                            <li class="mb-2"><a href="#">Review installed modules</a></li>
                            <li><a href="#">View tenant settings</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
