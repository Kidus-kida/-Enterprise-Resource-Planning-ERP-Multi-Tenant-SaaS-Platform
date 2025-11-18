@extends('layouts.app')

@section('page-content')
<div class="content container-fluid">
    <x-breadcrumb class="col">
        <x-slot name="title">{{ __('Follow-ups') }}</x-slot>
        <ul class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
            </li>
            <li class="breadcrumb-item active">{{ __('Follow-ups') }}</li>
        </ul>
    </x-breadcrumb>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ __('Follow-ups') }}</h4>
                    <a href="javascript:void(0)" class="btn btn-primary float-end" 
                       data-ajax-modal="true" data-url="{{ route('follow-ups.create') }}" 
                       data-title="{{ __('Add Follow-up') }}" data-size="md">
                        <i class="fa fa-plus"></i> {{ __('Add Follow-up') }}
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>{{ __('Title') }}</th>
                                    <th>{{ __('Lead') }}</th>
                                    <th>{{ __('Assigned To') }}</th>
                                    <th>{{ __('Follow-up Date') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($followUps as $followUp)
                                <tr>
                                    <td><a href="{{ route('follow-ups.show', encrypt($followUp->id)) }}">{{ $followUp->title }}</a></td>
                                    <td>{{ $followUp->lead->name }}</td>
                                    <td>{{ $followUp->assignedUser->name ?? 'Unassigned' }}</td>
                                    <td>{{ $followUp->follow_up_date->format('M d, Y H:i') }}</td>
                                    <td>
                                        <span class="badge badge-{{ $followUp->status == 'completed' ? 'success' : ($followUp->status == 'cancelled' ? 'danger' : 'warning') }}">
                                            {{ ucfirst($followUp->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('follow-ups.show', encrypt($followUp->id)) }}" class="text-info me-2" title="{{ __('View Details') }}">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        <a href="javascript:void(0)" class="text-primary me-2" 
                                           data-ajax-modal="true" data-url="{{ route('follow-ups.edit', $followUp) }}" 
                                           data-title="{{ __('Edit Follow-up') }}" data-size="md" title="{{ __('Edit') }}">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        <a href="javascript:void(0)" class="text-danger deleteBtn" 
                                           data-route="{{ route('follow-ups.destroy', $followUp) }}" 
                                           data-title="{{ __('Delete Follow-up') }}"
                                           data-question="{{ __('Are you sure you want to delete this follow-up?') }}" title="{{ __('Delete') }}">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">{{ __('No follow-ups found') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $followUps->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection