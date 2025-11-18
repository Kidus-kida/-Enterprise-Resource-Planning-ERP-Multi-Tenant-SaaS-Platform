@extends('layouts.app')

@section('page-content')
<div class="content container-fluid">
    <x-breadcrumb class="col">
        <x-slot name="title">{{ $campaign->title }}</x-slot>
        <ul class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('campaigns.index') }}">{{ __('Campaigns') }}</a>
            </li>
            <li class="breadcrumb-item active">{{ $campaign->title }}</li>
        </ul>
    </x-breadcrumb>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <a href="{{ route('campaigns.index') }}" class="btn btn-outline-secondary me-3">
                                <i class="fa fa-arrow-left"></i> {{ __('Back') }}
                            </a>
                            <h4 class="card-title mb-0">{{ __('Campaign Details') }}</h4>
                        </div>
                        <a href="javascript:void(0)" class="btn btn-primary" 
                           data-ajax-modal="true" data-url="{{ route('campaigns.edit', $campaign) }}" 
                           data-title="{{ __('Edit Campaign') }}" data-size="md">
                            <i class="fa fa-edit"></i> {{ __('Edit') }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>{{ __('Title') }}:</strong> {{ $campaign->title }}</p>
                            <p><strong>{{ __('Start Date') }}:</strong> {{ $campaign->start_date ? \Carbon\Carbon::parse($campaign->start_date)->format('M d, Y') : 'N/A' }}</p>
                            <p><strong>{{ __('End Date') }}:</strong> {{ $campaign->end_date ? \Carbon\Carbon::parse($campaign->end_date)->format('M d, Y') : 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>{{ __('Status') }}:</strong> 
                                <span class="badge badge-{{ $campaign->status == 'active' ? 'success' : ($campaign->status == 'completed' ? 'primary' : 'warning') }}">
                                    {{ ucfirst($campaign->status ?? 'draft') }}
                                </span>
                            </p>
                            <p><strong>{{ __('Created') }}:</strong> {{ $campaign->created_at->format('M d, Y') }}</p>
                        </div>
                        @if($campaign->description)
                        <div class="col-md-12">
                            <p><strong>{{ __('Description') }}:</strong></p>
                            <p>{{ $campaign->description }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection