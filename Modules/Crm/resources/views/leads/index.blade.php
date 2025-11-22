@extends('layouts.app')

@section('page-content')
<div class="content container-fluid">
    <x-breadcrumb class="col">
        <x-slot name="title">{{ __('Leads') }}</x-slot>
        <ul class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
            </li>
            <li class="breadcrumb-item active">{{ __('Leads') }}</li>
        </ul>
    </x-breadcrumb>

    <div class="row">
        <div class="col-md-12">
            <div class="card p-3">
                <div class="card-header">
                    <h4 class="card-title">{{ __('Leads') }}</h4>
                    <a href="javascript:void(0)" class="btn btn-primary float-end" 
                       data-ajax-modal="true" data-url="{{ route('leads.create') }}" 
                       data-title="{{ __('Add Lead') }}" data-size="md">
                        <i class="fa fa-plus"></i> {{ __('Add Lead') }}
                    </a>
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Email') }}</th>
                                    <th>{{ __('Phone') }}</th>
                                    <th>{{ __('Company') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Source') }}</th>
                                    <th>{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($leads as $lead)
                                <tr>
                                    <td><a href="{{ route('leads.show', encrypt($lead->id)) }}">{{ $lead->name }}</a></td>
                                    <td>{{ $lead->email }}</td>
                                    <td>{{ $lead->phone }}</td>
                                    <td>{{ $lead->company }}</td>
                                    <td>
                                        <span class="badge badge-{{ $lead->status == 'converted' ? 'success' : ($lead->status == 'lost' ? 'danger' : 'primary') }}">
                                            {{ ucfirst($lead->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $lead->source }}</td>
                                    <td>
                                        <a href="{{ route('leads.show', encrypt($lead->id)) }}" class="text-info me-2" title="{{ __('View Details') }}">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        <a href="javascript:void(0)" class="text-primary me-2" 
                                           data-ajax-modal="true" data-url="{{ route('leads.edit', $lead) }}" 
                                           data-title="{{ __('Edit Lead') }}" data-size="md" title="{{ __('Edit') }}">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        <a href="javascript:void(0)" class="text-danger deleteBtn" 
                                           data-route="{{ route('leads.destroy', $lead) }}" 
                                           data-title="{{ __('Delete Lead') }}"
                                           data-question="{{ __('Are you sure you want to delete this lead?') }}" title="{{ __('Delete') }}">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">{{ __('No leads found') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $leads->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
