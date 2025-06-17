@extends('layouts.app')
@section('page-content')
    <div class="content container-fluid">

        <!-- Page Header -->
        <x-breadcrumb class="col">
            <x-slot name="title">{{ __('Annual Leave') }}</x-slot>
            <ul class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
                </li>
                <li class="breadcrumb-item active">
                    {{ __('Employee Annual Leaves') }}
                </li>
            </ul>
            <x-slot name="right">
                <div class="col-auto float-end ms-auto">
                    {{-- POST request => generates balances for ALL employees --}}
                    <form action="{{ route('annual_leaves.store') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-plus"></i> {{ __('Generate Annual Leave') }}
                        </button>
                    </form>

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
    @vite(['resources/js/datatables.js', 'resources/assets/css/ckeditor.css', 'resources/js/ckeditor.js'])
    {!! $dataTable->scripts(attributes: ['type' => 'module']) !!}
@endpush
