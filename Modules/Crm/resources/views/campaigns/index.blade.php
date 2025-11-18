@extends('layouts.app')

@section('page-content')
<div class="content container-fluid">
    <x-breadcrumb class="col">
        <x-slot name="title">{{ __('Campaigns') }}</x-slot>
        <ul class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
            </li>
            <li class="breadcrumb-item active">{{ __('Campaigns') }}</li>
        </ul>
    </x-breadcrumb>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ __('Campaigns') }}</h4>
                    <a href="javascript:void(0)" class="btn btn-primary float-end" 
                       data-ajax-modal="true" data-url="{{ route('campaigns.create') }}" 
                       data-title="{{ __('Add Campaign') }}" data-size="md">
                        <i class="fa fa-plus"></i> {{ __('Add Campaign') }}
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>{{ __('Title') }}</th>
                                    <th>{{ __('Start Date') }}</th>
                                    <th>{{ __('End Date') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($campaigns as $campaign)
                                <tr>
                                    <td><a href="{{ route('campaigns.show', encrypt($campaign->id)) }}">{{ $campaign->title }}</a></td>
                                    <td>{{ $campaign->start_date ? \Carbon\Carbon::parse($campaign->start_date)->format('M d, Y') : 'N/A' }}</td>
                                    <td>{{ $campaign->end_date ? \Carbon\Carbon::parse($campaign->end_date)->format('M d, Y') : 'N/A' }}</td>
                                    <td>
                                        <span class="badge badge-{{ $campaign->status == 'active' ? 'success' : ($campaign->status == 'completed' ? 'primary' : 'warning') }}">
                                            {{ ucfirst($campaign->status ?? 'draft') }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('campaigns.show', encrypt($campaign->id)) }}" class="text-info me-2" title="{{ __('View Details') }}">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        <a href="javascript:void(0)" class="text-primary me-2" 
                                           data-ajax-modal="true" data-url="{{ route('campaigns.edit', $campaign) }}" 
                                           data-title="{{ __('Edit Campaign') }}" data-size="md" title="{{ __('Edit') }}">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        <a href="javascript:void(0)" class="text-danger deleteBtn" 
                                           data-route="{{ route('campaigns.destroy', $campaign) }}" 
                                           data-title="{{ __('Delete Campaign') }}"
                                           data-question="{{ __('Are you sure you want to delete this campaign?') }}" title="{{ __('Delete') }}">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center">{{ __('No campaigns found') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $campaigns->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection