@extends('layouts.app')

@section('page-content')
<div class="content container-fluid">

    <!-- Page Header -->
    <x-breadcrumb class="col">
        <x-slot name="title">{{ __('Stock-Adjustment Settings') }}</x-slot>
        <ul class="breadcrumb">
            <li class="breadcrumb-item active">{{ __('All stock adjustments') }}</li>
        </ul>
        <x-slot name="right">
            <div class="col-auto float-end ms-auto">
                <a href="" class="btn btn-secondary">
                    <i class="fa-solid fa-arrow-left"></i> {{ __('Back') }}
                </a>
            </div>
        </x-slot>
    </x-breadcrumb>
    <!-- /Page Header -->

    <div class="row">
        <div class="col-md-12">
            <div class="card p-3">
                <h5 class="mb-3">{{ __('Add Stock Setting ') }}</h5>

                <form action="{{ route('stockadjustment-settings.store') }}" method="POST">
                    @csrf
                    <div class="row">

                        <div class="col-md-4 mb-3">
                            <label>Date <span class="text-danger">*</span></label>
                            <input type="datetime-local"
                                   name="date"
                                   class="form-control"
                                   value="{{ !empty($settings->date) ? \Carbon\Carbon::parse($settings->date)->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i') }}"
                                   required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Adjustment Type <span class="text-danger">*</span></label>
                            <select name="adjustment_type" class="form-control" required>
                                <option value="increase" {{ (!empty($settings) && $settings->adjustment_type == 'increase') ? 'selected' : '' }}>Increase</option>
                                <option value="decrease" {{ (!empty($settings) && $settings->adjustment_type == 'decrease') ? 'selected' : '' }}>Decrease</option>
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Category</label>
                            <select name="category_id" class="form-control">
                                <option value="">Select Category</option>
                                @foreach($categories ?? [] as $id => $name)
                                    <option value="{{ $id }}" {{ (!empty($settings) && $settings->category_id == $id) ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Sub Category</label>
                            <select name="sub_category_id" class="form-control">
                                <option value="">Select Sub Category</option>
                                @foreach($sub_categories ?? [] as $id => $name)
                                    <option value="{{ $id }}" {{ (!empty($settings) && $settings->sub_category_id == $id) ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Account to Link</label>
                            <select name="account_to_link_id" class="form-control">
                                <option value="">Select Account</option>
                                @foreach($accounts ?? [] as $id => $name)
                                    <option value="{{ $id }}" {{ (!empty($settings) && $settings->account_to_link == $id) ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Stock Account Group</label>
                            <select name="stock_account_group_id" class="form-control">
                                <option value="">Select Account Group</option>
                                @foreach($stock_account_groups ?? [] as $id => $name)
                                    <option value="{{ $id }}" {{ (!empty($settings) && $settings->stock_group == $id) ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Stock Account</label>
                            <select name="stock_account_id" class="form-control">
                                <option value="">Select Stock Account</option>
                                @foreach($accounts ?? [] as $id => $name)
                                    <option value="{{ $id }}" {{ (!empty($settings) && $settings->stock_account == $id) ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>

                    </div>

                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                        <a href="{{ route('stock_adjustment.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
