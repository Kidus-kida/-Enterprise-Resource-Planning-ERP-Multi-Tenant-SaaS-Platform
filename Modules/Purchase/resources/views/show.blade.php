@extends('layouts.app')

@section('page-content')
    <div class="content container-fluid">
        <x-breadcrumb class="col">
            <x-slot name="title">{{ __('View Purchase') }}</x-slot>
            <ul class="breadcrumb">
                 <li class="breadcrumb-item"><a href="{{ route('purchase.index') }}">{{ __('Purchase') }}</a></li>
                 <li class="breadcrumb-item active">{{ __('View') }}</li>
            </ul>
        </x-breadcrumb>
        
        <div class="card">
            <div class="card-body">
                <h5>Purchase #{{ $transaction->ref_no }}</h5>
                <p>Status: {{ $transaction->status }}</p>
                <!-- Details here -->
            </div>
        </div>
    </div>
@endsection
