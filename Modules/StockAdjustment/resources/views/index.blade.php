@extends('layouts.app')


@section('page-content')
    <div class="content container-fluid">

        <!-- Page Header -->
        <x-breadcrumb class="col">
            <x-slot name="title">{{ __('Stock-Adjustment') }}</x-slot>
            <ul class="breadcrumb">
                <!-- <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}">{{ __('Stock-Adjustment') }}</a>
                </li> -->
                <li class="breadcrumb-item active">
                    {{ __('All stock adjustments') }}
                </li>
            </ul>
            <x-slot name="right">
                <div class="col-auto float-end ms-auto">
                   <a href="{{ route('stock_adjustment.create') }}" class="btn add-btn">
    <i class="fa-solid fa-plus"></i> {{ __('Add') }}
</a>

                </div>
            </x-slot>
        </x-breadcrumb>
        <!-- /Page Header -->

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
