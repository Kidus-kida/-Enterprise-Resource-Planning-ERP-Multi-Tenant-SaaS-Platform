@extends('layouts.app')


@section('page-content')
<div class="content container-fluid">


    <x-breadcrumb class="col">
        <x-slot name="title">{{ __('Tax Rate Details') }}</x-slot>
        <ul class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('payroll.tax.index') }}">{{ __('Tax Rates') }}</a>
            </li>
            <li class="breadcrumb-item active">
                {{ __('View Tax Rate') }}
            </li>
        </ul>

        <x-slot name="right">
            <div class="col-auto float-end ms-auto">
                <button class="btn btn-light border"
                    onclick='window.location.href="{{ route('payroll.tax.index') }}"'>
                    <i class="fa-solid fa-arrow-left"></i> {{ __('Go Back') }}
                </button>
            </div>
        </x-slot>
    </x-breadcrumb>


    <div class="row">
        <div class="col-md-10 mx-auto">
            <div class="card shadow-sm border-0">

                <div class="card-header bg-white py-3 border-bottom">
                    <h4 class="card-title mb-0">
                        <i class="fa-solid fa-percent text-primary me-2"></i>
                        {{ __('Tax Range Details') }}
                    </h4>
                </div>

                <div class="card-body">

                    
                    <div class="row g-3">

                        <div class="col-md-6">
                            <div class="p-3 rounded border bg-light">
                                <small class="text-muted">{{ __('Salary From') }}</small>
                                <h5 class="mb-0">
                                    {{ number_format($tax->salary_from, 2) }} Br
                                </h5>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="p-3 rounded border bg-light">
                                <small class="text-muted">{{ __('Salary To') }}</small>
                                <h5 class="mb-0">
                                    @if($tax->salary_to)
                                        {{ number_format($tax->salary_to, 2) }} Br
                                    @else
                                        <em>{{ __('No upper limit') }}</em>
                                    @endif
                                </h5>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="p-3 rounded border bg-light">
                                <small class="text-muted">{{ __('Percentage') }}</small>
                                <h5 class="mb-0">
                                    {{ $tax->percentage }}%
                                </h5>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="p-3 rounded border bg-light">
                                <small class="text-muted">{{ __('Deducted Amount') }}</small>
                                <h5 class="mb-0">
                                    {{ number_format($tax->deducted_amount, 2) }} Br
                                </h5>
                            </div>
                        </div>

                    </div>

                    <hr class="my-4">

                   
                    <div class="d-flex gap-2">
                        <a href="javascript:void(0)"
                           data-url="{{ route('payroll.tax.edit', $tax->id) }}"
                           class="btn btn-primary"
                           data-ajax-modal="true"
                           data-size="md"
                           data-title="{{ __('Edit Tax Rate') }}">
                            <i class="fa-solid fa-pencil"></i> {{ __('Edit') }}
                        </a>

                        <a href="javascript:void(0)"
                           class="btn btn-outline-danger deleteBtn"
                           data-route="{{ route('payroll.tax.destroy', $tax->id) }}"
                           data-title="{{ __('Delete Tax Range') }}"
                           data-question="{{ __('Are you sure you want to delete this tax range?') }}">
                            <i class="fa-regular fa-trash-can"></i> {{ __('Delete') }}
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>

</div>
@endsection


@push('page-scripts')

@endpush
