@extends('layouts.app')

@section('page-content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <x-breadcrumb>
            <x-slot name="title">{{ __('Payroll Processing') }}</x-slot>
            <x-slot name="right">
                <div class="col-auto float-end ms-auto">
                    <a href="{{ route('payroll.processing.create') }}" class="btn btn-primary">
                        <i class="fa fa-plus"></i> {{ __('Add Payroll') }}
                    </a>
                </div>
            </x-slot>
        </x-breadcrumb>
        <!-- /Page Header -->

        <!-- Payroll Table -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>{{ __('Batch Number') }}</th>
                                        <th>{{ __('Period') }}</th>
                                        <th>{{ __('Pay Date') }}</th>
                                        <th>{{ __('Employees') }}</th>
                                        <th>{{ __('Total Gross') }}</th>
                                        <th>{{ __('Total Net') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Created By') }}</th>
                                        <th>{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($batches as $batch)
                                        <tr>
                                            <td><strong>{{ $batch->batch_number }}</strong></td>
                                            <td>{{ format_date($batch->period_start) }} - {{ format_date($batch->period_end) }}</td>
                                            <td>{{ format_date($batch->pay_date) }}</td>
                                            <td>{{ $batch->total_employees }}</td>
                                            <td>{{ LocaleSettings('currency_symbol') }} {{ number_format($batch->total_gross, 2) }}</td>
                                            <td>{{ LocaleSettings('currency_symbol') }} {{ number_format($batch->total_net, 2) }}</td>
                                            <td>
                                                @if($batch->status == 'draft')
                                                    <span class="badge bg-warning">{{ __('Draft') }}</span>
                                                @elseif($batch->status == 'approved')
                                                    <span class="badge bg-success">{{ __('Approved') }}</span>
                                                @else
                                                    <span class="badge bg-info">{{ __('Paid') }}</span>
                                                @endif
                                            </td>
                                            <td>{{ $batch->creator->fullname ?? '-' }}</td>
                                            <td>
                                                <a href="{{ route('payroll.processing.show', $batch->id) }}" class="btn btn-sm btn-primary">
                                                    <i class="fa fa-eye"></i> {{ __('View') }}
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center text-muted">{{ __('No payroll batches found') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        {{ $batches->links() }}
                    </div>
                </div>
            </div>
        </div>
        <!-- /Payroll Table -->
    </div>
@endsection
