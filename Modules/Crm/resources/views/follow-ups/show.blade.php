@extends('layouts.app')

@section('page-content')
<div class="content container-fluid">
    <x-breadcrumb class="col">
        <x-slot name="title">{{ $followUp->title }}</x-slot>
        <ul class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('follow-ups.index') }}">{{ __('Follow-ups') }}</a>
            </li>
            <li class="breadcrumb-item active">{{ $followUp->title }}</li>
        </ul>
    </x-breadcrumb>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <a href="{{ route('follow-ups.index') }}" class="btn btn-outline-secondary me-3">
                                <i class="fa fa-arrow-left"></i> {{ __('Back') }}
                            </a>
                            <h4 class="card-title mb-0">{{ __('Follow-up Details') }}</h4>
                        </div>
                        <a href="javascript:void(0)" class="btn btn-primary" 
                           data-ajax-modal="true" data-url="{{ route('follow-ups.edit', $followUp) }}" 
                           data-title="{{ __('Edit Follow-up') }}" data-size="md">
                            <i class="fa fa-edit"></i> {{ __('Edit') }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>{{ __('Title') }}:</strong> {{ $followUp->title }}</p>
                            <p><strong>{{ __('Lead') }}:</strong> 
                                <a href="{{ route('leads.show', $followUp->lead) }}">{{ $followUp->lead->name }}</a>
                            </p>
                            <p><strong>{{ __('Follow-up Date') }}:</strong> {{ $followUp->follow_up_date->format('M d, Y H:i') }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>{{ __('Status') }}:</strong> 
                                <span class="badge badge-{{ $followUp->status == 'completed' ? 'success' : ($followUp->status == 'cancelled' ? 'danger' : 'warning') }}">
                                    {{ ucfirst($followUp->status) }}
                                </span>
                            </p>
                            <p><strong>{{ __('Assigned To') }}:</strong> {{ $followUp->assignedUser->name ?? 'Unassigned' }}</p>
                            <p><strong>{{ __('Created') }}:</strong> {{ $followUp->created_at->format('M d, Y') }}</p>
                        </div>
                        @if($followUp->description)
                        <div class="col-md-12">
                            <p><strong>{{ __('Description') }}:</strong></p>
                            <p>{{ $followUp->description }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection