@extends('layouts.app')

@section('title', $pageTitle)

@section('page-content')
    <div class="content container-fluid">
        
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{ $pageTitle }}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('shifts.index') }}">{{ __('Shifts') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Rotation Plans') }}</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <div class="d-flex gap-2">
                        <a href="{{ route('shifts.index') }}" class="btn btn-outline-secondary">
                            <i class="la la-cog"></i> {{ __('Shift Management') }}
                        </a>
                        <a href="{{ route('shifts.rotation.forecast') }}" class="btn btn-outline-success">
                            <i class="la la-calendar"></i> {{ __('Forecast Schedule') }}
                        </a>
                        <a href="{{ route('shifts.rotation.create') }}" class="btn btn-primary">
                            <i class="la la-plus"></i> {{ __('Create Rotation Plan') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th>{{ __('Plan Name') }}</th>
                                        <th>{{ __('Frequency') }}</th>
                                        <th>{{ __('Steps') }}</th>
                                        <th>{{ __('Start Date') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($rotations as $rotation)
                                        <tr>
                                            <td>
                                                <strong>{{ $rotation->name }}</strong>
                                                @if($rotation->description)
                                                    <br><small class="text-muted">{{ $rotation->description }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-info-light text-info">
                                                    {{ $rotation->frequency_interval }} {{ ucfirst($rotation->frequency_type) }}(s)
                                                </span>
                                            </td>
                                            <td>
                                                <a href="{{ route('shifts.rotation.edit', $rotation->id) }}" class="text-decoration-none">
                                                    <span class="badge bg-primary-light text-primary" style="cursor: pointer;">
                                                        {{ $rotation->steps_count }} {{ __('Shifts') }}
                                                    </span>
                                                </a>
                                            </td>
                                            <td>{{ $rotation->start_date->format('M d, Y') }}</td>
                                            <td>
                                                @if($rotation->is_active)
                                                    <span class="badge badge-success">{{ __('Active') }}</span>
                                                @else
                                                    <span class="badge badge-secondary">{{ __('Inactive') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="dropdown dropdown-action">
                                                    <a href="#" class="action-icon dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="la la-ellipsis-h"></i>
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a class="dropdown-item" href="{{ route('shifts.rotation.edit', $rotation->id) }}">
                                                            <i class="la la-pencil m-r-5"></i> {{ __('Edit') }}
                                                        </a>
                                                        <form action="{{ route('shifts.rotation.destroy', $rotation->id) }}" method="POST" class="d-inline" 
                                                              onsubmit="return confirm('{{ __('Are you sure you want to delete this rotation plan?') }}')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="dropdown-item">
                                                                <i class="la la-trash m-r-5"></i> {{ __('Delete') }}
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center">
                                                <div class="py-5">
                                                    <i class="la la-sync" style="font-size: 48px; color: #ccc;"></i>
                                                    <p class="text-muted mt-3">{{ __('No rotation plans created yet') }}</p>
                                                    <a href="{{ route('shifts.rotation.create') }}" class="btn btn-primary btn-sm mt-2">
                                                        <i class="la la-plus"></i> {{ __('Create Your First Plan') }}
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
