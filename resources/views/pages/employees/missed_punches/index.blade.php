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
                        <li class="breadcrumb-item active">{{ __('Missed Punches') }}</li>
                    </ul>
                </div>
                <div class="col-auto float-end ms-auto">
                    <a href="{{ route('missed-punches.create') }}" class="btn add-btn">
                        <i class="fa fa-plus"></i> {{ __('New Request') }}
                    </a>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <div class="row">
            <div class="col-md-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped custom-table mb-0">
                                <thead>
                                    <tr>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Type') }}</th>
                                        <th>{{ __('Requested Times') }}</th>
                                        <th>{{ __('Reason') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Created At') }}</th>
                                        <th class="text-end">{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($requests as $request)
                                        <tr>
                                            <td>{{ $request->date->format('M d, Y') }}</td>
                                            <td>
                                                @if($request->punch_type === 'clock_in')
                                                    <span class="badge bg-inverse-info">{{ __('Clock-In') }}</span>
                                                @elseif($request->punch_type === 'clock_out')
                                                    <span class="badge bg-inverse-warning">{{ __('Clock-Out') }}</span>
                                                @else
                                                    <span class="badge bg-inverse-purple">{{ __('Both') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($request->punch_type === 'clock_in')
                                                    {{ $request->requested_start_time->format('H:i') }}
                                                @elseif($request->punch_type === 'clock_out')
                                                    {{ $request->requested_end_time->format('H:i') }}
                                                @else
                                                    {{ $request->requested_start_time->format('H:i') }} - {{ $request->requested_end_time->format('H:i') }}
                                                @endif
                                            </td>
                                            <td>
                                                <span title="{{ $request->reason }}">
                                                    {{ Str::limit($request->reason, 30) }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="dropdown action-label">
                                                    @if($request->status === 'pending')
                                                        <a class="btn btn-white btn-sm btn-rounded" href="javascript:void(0);">
                                                            <i class="fa fa-dot-circle-o text-warning"></i> {{ __('Pending') }}
                                                        </a>
                                                    @elseif($request->status === 'approved')
                                                        <a class="btn btn-white btn-sm btn-rounded" href="javascript:void(0);">
                                                            <i class="fa fa-dot-circle-o text-success"></i> {{ __('Approved') }}
                                                        </a>
                                                    @else
                                                        <a class="btn btn-white btn-sm btn-rounded" href="javascript:void(0);">
                                                            <i class="fa fa-dot-circle-o text-danger"></i> {{ __('Rejected') }}
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>{{ $request->created_at->format('M d, Y H:i') }}</td>
                                            <td class="text-end">
                                                @if($request->status === 'pending')
                                                    <div class="dropdown dropdown-action">
                                                        <a href="#" class="action-icon dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><i class="material-icons">more_vert</i></a>
                                                        <div class="dropdown-menu dropdown-menu-right">
                                                            <a class="dropdown-item" href="javascript:void(0);" onclick="confirmDelete({{ $request->id }})"><i class="fa fa-trash-o m-r-5"></i> {{ __('Cancel') }}</a>
                                                        </div>
                                                    </div>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4 text-muted">
                                                {{ __('No missed punch requests found.') }}
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if($requests->hasPages())
                        <div class="card-footer bg-white border-top-0">
                            {{ $requests->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
