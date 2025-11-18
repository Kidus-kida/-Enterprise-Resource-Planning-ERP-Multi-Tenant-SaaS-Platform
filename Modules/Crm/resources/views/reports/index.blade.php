@extends('layouts.app')

@section('page-content')
<div class="content container-fluid">
    <x-breadcrumb class="col">
        <x-slot name="title">{{ __('CRM Reports') }}</x-slot>
        <ul class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
            </li>
            <li class="breadcrumb-item active">{{ __('CRM Reports') }}</li>
        </ul>
    </x-breadcrumb>

    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">{{ __('Total Leads') }}</h5>
                    <h2 class="text-primary">{{ $totalLeads }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">{{ __('New Leads') }}</h5>
                    <h2 class="text-info">{{ $newLeads }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">{{ __('Converted') }}</h5>
                    <h2 class="text-success">{{ $convertedLeads }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">{{ __('Lost Leads') }}</h5>
                    <h2 class="text-danger">{{ $lostLeads }}</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Follow-up Statistics -->
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">{{ __('Total Follow-ups') }}</h5>
                    <h2 class="text-primary">{{ $totalFollowUps }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">{{ __('Pending Follow-ups') }}</h5>
                    <h2 class="text-warning">{{ $pendingFollowUps }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">{{ __('Completed Follow-ups') }}</h5>
                    <h2 class="text-success">{{ $completedFollowUps }}</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Campaign Statistics -->
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">{{ __('Total Campaigns') }}</h5>
                    <h2 class="text-primary">{{ $totalCampaigns }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">{{ __('Active Campaigns') }}</h5>
                    <h2 class="text-success">{{ $activeCampaigns }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">{{ __('Completed Campaigns') }}</h5>
                    <h2 class="text-info">{{ $completedCampaigns }}</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Leads -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ __('Recent Leads') }}</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Company') }}</th>
                                    <th>{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentLeads as $lead)
                                <tr>
                                    <td><a href="{{ route('leads.show', encrypt($lead->id)) }}">{{ $lead->name }}</a></td>
                                    <td>{{ $lead->company ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge badge-{{ $lead->status == 'converted' ? 'success' : ($lead->status == 'lost' ? 'danger' : 'primary') }}">
                                            {{ ucfirst($lead->status) }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center">{{ __('No leads found') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upcoming Follow-ups -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ __('Upcoming Follow-ups') }}</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>{{ __('Title') }}</th>
                                    <th>{{ __('Lead') }}</th>
                                    <th>{{ __('Date') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($upcomingFollowUps as $followUp)
                                <tr>
                                    <td><a href="{{ route('follow-ups.show', encrypt($followUp->id)) }}">{{ $followUp->title }}</a></td>
                                    <td>{{ $followUp->lead->name }}</td>
                                    <td>{{ $followUp->follow_up_date->format('M d, Y') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center">{{ __('No upcoming follow-ups') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Campaigns -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ __('Recent Campaigns') }}</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>{{ __('Title') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Start Date') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentCampaigns as $campaign)
                                <tr>
                                    <td><a href="{{ route('campaigns.show', encrypt($campaign->id)) }}">{{ $campaign->title }}</a></td>
                                    <td>
                                        <span class="badge badge-{{ $campaign->status == 'active' ? 'success' : ($campaign->status == 'completed' ? 'primary' : 'warning') }}">
                                            {{ ucfirst($campaign->status ?? 'draft') }}
                                        </span>
                                    </td>
                                    <td>{{ $campaign->start_date ? \Carbon\Carbon::parse($campaign->start_date)->format('M d, Y') : 'N/A' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center">{{ __('No campaigns found') }}</td>
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