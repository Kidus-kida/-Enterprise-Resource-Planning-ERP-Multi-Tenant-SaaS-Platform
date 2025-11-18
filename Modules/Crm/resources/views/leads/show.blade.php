@extends('layouts.app')

@section('page-content')
<div class="content container-fluid">
    <x-breadcrumb class="col">
        <x-slot name="title">{{ $lead->name }}</x-slot>
        <ul class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('leads.index') }}">{{ __('Leads') }}</a>
            </li>
            <li class="breadcrumb-item active">{{ $lead->name }}</li>
        </ul>
    </x-breadcrumb>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <a href="{{ route('leads.index') }}" class="btn btn-outline-secondary me-3">
                                <i class="fa fa-arrow-left"></i> {{ __('Back') }}
                            </a>
                            <h4 class="card-title mb-0">{{ __('Lead Details') }}</h4>
                        </div>
                        <a href="javascript:void(0)" class="btn btn-primary" 
                           data-ajax-modal="true" data-url="{{ route('leads.edit', $lead) }}" 
                           data-title="{{ __('Edit Lead') }}" data-size="md">
                            <i class="fa fa-edit"></i> {{ __('Edit') }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>{{ __('Name') }}:</strong> {{ $lead->name }}</p>
                            <p><strong>{{ __('Email') }}:</strong> {{ $lead->email ?? 'N/A' }}</p>
                            <p><strong>{{ __('Phone') }}:</strong> {{ $lead->phone ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>{{ __('Company') }}:</strong> {{ $lead->company ?? 'N/A' }}</p>
                            <p><strong>{{ __('Status') }}:</strong> 
                                <span class="badge badge-{{ $lead->status == 'converted' ? 'success' : ($lead->status == 'lost' ? 'danger' : 'primary') }}">
                                    {{ ucfirst($lead->status) }}
                                </span>
                            </p>
                            <p><strong>{{ __('Source') }}:</strong> {{ $lead->source ?? 'N/A' }}</p>
                        </div>
                        @if($lead->notes)
                        <div class="col-md-12">
                            <p><strong>{{ __('Notes') }}:</strong></p>
                            <p>{{ $lead->notes }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection