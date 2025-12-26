@extends('layouts.app')

@section('page-content')
    <div class="content container-fluid">
        <x-breadcrumb>
            <x-slot name="title">{{ __('Edit Sale') }}</x-slot>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('sales.index') }}">{{ __('Sales') }}</a></li>
                <li class="breadcrumb-item active">{{ __('Edit') }}</li>
            </ul>
        </x-breadcrumb>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <p>{{ __('Edit form will appear here.') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
