@extends('layouts.app')


@section('page-content')
    <div class="content container-fluid">
        @include('leave.partials.nav')

        <!-- Page Header -->
        <x-breadcrumb class="col">
            <x-slot name="title">{{ __('Holidays') }}</x-slot>
            <ul class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
                </li>
                <li class="breadcrumb-item active">
                    {{ __('Holidays') }}
                </li>
            </ul>
            <x-slot name="right">
                <div class="col-auto float-end ms-auto">
                    <a data-url="{{ route('leave.config.public-holidays.create') }}" href="javascript:void(0)" class="btn add-btn"
                        data-ajax-modal="true" data-size="lg" data-title="Add Holiday">
                        <i class="fa-solid fa-plus"></i> {{ __('Add Holiday') }}
                    </a>
                    <div class="view-icons">
                        <a href="{{ route('leave.config.public-holidays.index') }}" data-bs-toggle="tooltip" data-bs-title="{{ __("Holidays List") }}" class="list-view btn btn-link active"><i class="fa-solid fa-bars"></i></a>
                        <a href="{{ route('leave.config.public-holidays.calendar') }}" data-bs-toggle="tooltip" data-bs-title="{{ __("Holidays Calendar") }}" class="grid-view btn btn-link"><i class="fa fa-calendar"></i></a>
                    </div>
                </div>
            </x-slot>
        </x-breadcrumb>
        <!-- /Page Header -->

        <!-- Search Filter -->
        <!-- /Search Filter -->

        <div class="row">
            <div class="col-md-12">
                <div class="table-responsive">
                    {!! $dataTable->table(['class' => 'table table-striped custom-table w-100']) !!}
                </div>
            </div>
        </div>
    </div>
@endsection


@push('page-scripts')
@vite([
    "resources/js/datatables.js"
])
{!! $dataTable->scripts(attributes: ['type' => 'module']) !!}
@endpush
