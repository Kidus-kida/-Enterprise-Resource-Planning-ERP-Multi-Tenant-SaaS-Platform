@extends('layouts.app')

@section('page-content')
    <div class="content container-fluid">
        <x-breadcrumb class="col">
            <x-slot name="title">{{ __('Edit Purchase') }}</x-slot>
            <ul class="breadcrumb">
                 <li class="breadcrumb-item"><a href="{{ route('purchase.index') }}">{{ __('Purchase') }}</a></li>
                 <li class="breadcrumb-item active">{{ __('Edit') }}</li>
            </ul>
        </x-breadcrumb>
        
        <div class="alert alert-warning">
            Edit functionality is currently a placeholder.
        </div>
    </div>
@endsection
