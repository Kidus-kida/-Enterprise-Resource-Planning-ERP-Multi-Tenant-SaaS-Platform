@extends('layouts.app')
@section('title', __('Open Cash Register'))

@section('page-content')
    <div class="content container-fluid">
        <x-breadcrumb class="col">
            <x-slot name="title">{{ __('Open Cash Register') }}</x-slot>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                <li class="breadcrumb-item active">{{ __('Open Cash Register') }}</li>
            </ul>
        </x-breadcrumb>

        <div class="row">
            <div class="col-md-6 offset-md-3">
                <div class="card card-flush shadow-sm">
                    <div class="card-body">
                        <form action="{{ route('sales.cash-register.store') }}" method="POST" id="add_cash_register_form">
                            @csrf
                            <div class="mb-5">
                                <label for="amount" class="form-label fw-bold">{{ __('Cash in hand') }}:*</label>
                                <input type="number" name="amount" id="amount" class="form-control" placeholder="{{ __('Enter amount') }}" step="0.01" required>
                            </div>

                            @if($business_locations->count() > 1)
                                <div class="mb-5">
                                    <label for="location_id" class="form-label fw-bold">{{ __('Business Location') }}:*</label>
                                    <select name="location_id" id="location_id" class="form-select select2" required>
                                        <option value="" disabled selected>-- {{ __('Select Location') }} --</option>
                                        @foreach($business_locations as $id => $name)
                                            <option value="{{ $id }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @else
                                <input type="hidden" name="location_id" value="{{ array_key_first($business_locations->toArray()) }}">
                            @endif

                            <div class="d-grid mt-8">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    {{ __('Open Register') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
