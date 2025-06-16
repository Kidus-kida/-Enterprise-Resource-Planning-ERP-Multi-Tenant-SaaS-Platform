@extends('layouts.app')

@section('page-content')
    <div class="content container-fluid">

        <!-- Page Header -->
        <x-breadcrumb class="col">
            <x-slot name="title">{{ __('Leave Types') }}</x-slot>
            <ul class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
                </li>
                <li class="breadcrumb-item active">
                    {{ __('Leave Types') }}
                </li>
            </ul>
            <x-slot name="buttons">

                <a href="javascript:void(0)" data-url="{{ route('leavetypes.create') }}" class="btn add-btn"
                    data-ajax-modal="true" data-size="lg" data-title="{{ __('Add Leave Type') }}">
                    <i class="fa-solid fa-plus"></i> {{ __('Add Leav Type') }}
                </a>
             
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
    @vite(['resources/js/datatables.js', 'resources/assets/css/ckeditor.css', 'resources/js/ckeditor.js'])
    {!! $dataTable->scripts(attributes: ['type' => 'module']) !!}
@endpush
