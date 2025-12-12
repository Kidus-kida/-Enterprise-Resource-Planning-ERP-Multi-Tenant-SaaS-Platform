@extends('layouts.app')

@section('page-content')
@php
  $business_id = request()->session()->get('user.business_id');
  if(empty($business_id)){
      $business_id = auth()->user()->business_id;
  }
  $business = \App\Business::find($business_id);
@endphp
<div class="content container-fluid">
    <x-breadcrumb class="col">
        <x-slot name="title">{{ __('product.add_new_product') }}</x-slot>
        <ul class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('products.index') }}">{{ __('product.products') }}</a>
            </li>
            <li class="breadcrumb-item active">{{ __('product.add_new_product') }}</li>
        </ul>
    </x-breadcrumb>

    @php
        $form_class = empty($duplicate_product) ? 'create' : '';
        $is_image_required = !empty($common_settings['is_product_image_required']);
    @endphp

    <form action="{{ route('products.store') }}" method="POST" id="product_add_form"
        class="product_form {{ $form_class }}" enctype="multipart/form-data">
        @csrf

        <!-- Basic Product Information -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">{{ __('product.product_information') }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label" for="name">{{ __('product.product_name') }} <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="name" id="name" class="form-control" required
                                        value="{{ old('name', !empty($duplicate_product->name) ? $duplicate_product->name : null) }}"
                                        placeholder="{{ __('product.product_name') }}">
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label" for="sku">{{ __('product.sku') }}</label>
                                    <input type="text" name="sku" id="sku" class="form-control"
                                        value="{{ old('sku') }}" placeholder="{{ __('product.sku') }}">
                                    <small class="text-muted">{{ __('tooltip.sku') }}</small>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label" for="barcode_type">{{ __('product.barcode_type') }} <span
                                            class="text-danger">*</span></label>
                                    <select name="barcode_type" id="barcode_type" class="form-control select2" required>
                                        @foreach ($barcode_types as $key => $type)
                                            <option value="{{ $key }}"
                                                {{ old('barcode_type', !empty($duplicate_product->barcode_type) ? $duplicate_product->barcode_type : $barcode_default) == $key ? 'selected' : '' }}>
                                                {{ $type }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label" for="semi_finished">{{ __('unit.semi_finished') }} <span
                                            class="text-danger">*</span></label>
                                    <select name="semi_finished" id="semi_finished" class="form-control" required>
                                        <option value="">{{ __('messages.please_select') }}</option>
                                        <option value="1" {{ old('semi_finished') == '1' ? 'selected' : '' }}>
                                            {{ __('messages.yes') }}
                                        </option>
                                        <option value="0" {{ old('semi_finished', '0') == '0' ? 'selected' : '' }}>
                                            {{ __('messages.no') }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mt-2">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label" for="unit_id">{{ __('product.unit') }} <span
                                            class="text-danger">*</span></label>
                                    <select name="unit_id" id="unit_id" class="form-control select2" required>
                                        @foreach ($units as $id => $unit)
                                            <option value="{{ $id }}"
                                                {{ old('unit_id', !empty($duplicate_product->unit_id) ? $duplicate_product->unit_id : $business->default_unit) == $id ? 'selected' : '' }}>
                                                {{ $unit }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3 @if (!$business->enable_sub_units) d-none @endif">
                                <div class="mb-3">
                                    <label class="form-label" for="sub_unit_ids">{{ __('lang_v1.related_sub_units') }}</label>
                                    <select name="sub_unit_ids[]" id="sub_unit_ids" class="form-control select2" multiple>
                                        @if (!empty($duplicate_product->sub_unit_ids))
                                            @foreach ($duplicate_product->sub_unit_ids as $sub_unit_id)
                                                <option value="{{ $sub_unit_id }}" selected>{{ $sub_unit_id }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <small class="text-muted">{{ __('lang_v1.sub_units_tooltip') }}</small>
                                </div>
                            </div>

                            @if (!empty($common_settings['enable_secondary_unit']))
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label" for="secondary_unit_id">{{ __('lang_v1.secondary_unit') }}</label>
                                        <select name="secondary_unit_id" id="secondary_unit_id" class="form-control select2">
                                            <option value="">{{ __('messages.please_select') }}</option>
                                            @foreach ($units as $id => $unit)
                                                <option value="{{ $id }}"
                                                    {{ old('secondary_unit_id', !empty($duplicate_product->secondary_unit_id) ? $duplicate_product->secondary_unit_id : null) == $id ? 'selected' : '' }}>
                                                    {{ $unit }}</option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted">{{ __('lang_v1.secondary_unit_help') }}</small>
                                    </div>
                                </div>
                            @endif

                            <div class="col-md-3 @if (!$business->enable_brand) d-none @endif">
                                <div class="mb-3">
                                    <label class="form-label" for="brand_id">{{ __('product.brand') }}</label>
                                    <select name="brand_id" id="brand_id" class="form-control select2">
                                        <option value="">{{ __('messages.please_select') }}</option>
                                        @foreach ($brands as $id => $brand)
                                            <option value="{{ $id }}"
                                                {{ old('brand_id', !empty($duplicate_product->brand_id) ? $duplicate_product->brand_id : null) == $id ? 'selected' : '' }}>
                                                {{ $brand }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3 @if (!$business->enable_category) d-none @endif">
                                <div class="mb-3">
                                    <label class="form-label" for="category_id">{{ __('product.category') }}</label>
                                    <select name="category_id" id="category_id" class="form-control select2">
                                        <option value="">{{ __('messages.please_select') }}</option>
                                        @foreach ($categories as $id => $category)
                                            <option value="{{ $id }}"
                                                {{ old('category_id', !empty($duplicate_product->category_id) ? $duplicate_product->category_id : null) == $id ? 'selected' : '' }}>
                                                {{ $category }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div
                                class="col-md-3 @if (!($business->enable_category && $business->enable_sub_category)) d-none @endif">
                                <div class="mb-3">
                                    <label class="form-label" for="sub_category_id">{{ __('product.sub_category') }}</label>
                                    <select name="sub_category_id" id="sub_category_id" class="form-control select2">
                                        <option value="">{{ __('messages.please_select') }}</option>
                                        @foreach ($sub_categories as $id => $sub_cat)
                                            <option value="{{ $id }}"
                                                {{ old('sub_category_id', !empty($duplicate_product->sub_category_id) ? $duplicate_product->sub_category_id : null) == $id ? 'selected' : '' }}>
                                                {{ $sub_cat }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mt-2">
                            @php
                                $default_location = null;
                                if (count($business_locations) == 1) {
                                    $default_location = array_key_first($business_locations->toArray());
                                }
                            @endphp
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label" for="product_locations">{{ __('business.business_locations') }}</label>
                                    <select name="product_locations[]" id="product_locations" class="form-control select2"
                                        multiple>
                                        @foreach ($business_locations as $id => $location)
                                            <option value="{{ $id }}"
                                                {{ $default_location == $id ? 'selected' : '' }}>{{ $location }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">{{ __('lang_v1.product_location_help') }}</small>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="mb-3">
                                    <br>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="enable_stock" id="enable_stock"
                                            value="1"
                                            {{ old('enable_stock', !empty($duplicate_product) ? $duplicate_product->enable_stock : true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="enable_stock">
                                            <strong>{{ __('product.manage_stock') }}</strong>
                                        </label>
                                    </div>
                                    <small class="text-muted"><i>{{ __('product.enable_stock_help') }}</i></small>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="mb-3">
                                    <br>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="vat_claimed" id="vat_claimed"
                                            value="1"
                                            {{ old('vat_claimed', !empty($duplicate_product) ? $duplicate_product->vat_claimed : true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="vat_claimed">
                                            <strong>{{ __('product.vat_input_claimed') }}</strong>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3 @if (!empty($duplicate_product) && $duplicate_product->enable_stock == 0) d-none @endif"
                                id="alert_quantity_div">
                                <div class="mb-3">
                                    <label class="form-label" for="alert_quantity">{{ __('product.alert_quantity') }}</label>
                                    <input type="text" name="alert_quantity" id="alert_quantity"
                                        class="form-control input_number" min="0"
                                        value="{{ old('alert_quantity', !empty($duplicate_product->alert_quantity) ? @format_quantity($duplicate_product->alert_quantity) : null) }}"
                                        placeholder="{{ __('product.alert_quantity') }}">
                                    <small class="text-muted">{{ __('tooltip.alert_quantity') }}</small>
                                </div>
                            </div>

                            @if (!empty($common_settings['enable_product_warranty']))
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label" for="warranty_id">{{ __('lang_v1.warranty') }}</label>
                                        <select name="warranty_id" id="warranty_id" class="form-control select2">
                                            <option value="">{{ __('messages.please_select') }}</option>
                                            @foreach ($warranties as $id => $warranty)
                                                <option value="{{ $id }}">{{ $warranty }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="row g-3 mt-2">
                            <div class="col-md-3 @if (!empty($duplicate_product) && $duplicate_product->enable_stock == 0) d-none @endif"
                                id="raw_material_div">
                                <div class="mb-3">
                                    <label class="form-label" for="stock_type">{{ __('product.stock_type') }}</label>
                                    <select name="stock_type" id="stock_type" class="form-control select2" required>
                                        <option value="">{{ __('product.please_select') }}</option>
                                        @foreach ($accounts as $id => $account)
                                            <option value="{{ $id }}">{{ $account }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label" for="product_description">{{ __('lang_v1.product_description') }}</label>
                                    <textarea name="product_description" id="product_description" class="form-control"
                                        rows="3">{{ old('product_description', !empty($duplicate_product->product_description) ? $duplicate_product->product_description : null) }}</textarea>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label" for="upload_image">{{ __('lang_v1.product_image') }}</label>
                                    <input type="file" name="image" id="upload_image" accept="image/*"
                                        {{ $is_image_required ? 'required' : '' }} class="form-control">
                                    <small class="text-muted">
                                        {{ __('purchase.max_file_size', ['size' => config('constants.document_size_limit') / 1000000]) }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Advanced Options -->
        <div class="row mt-3">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">{{ __('product.advanced_options') }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @if ($business->enable_product_expiry)
                                @if ($business->expiry_type == 'add_expiry')
                                    @php
                                        $expiry_period = 12;
                                        $hide = true;
                                    @endphp
                                @else
                                    @php
                                        $expiry_period = null;
                                        $hide = false;
                                    @endphp
                                @endif
                                <div class="col-md-4 @if ($hide) d-none @endif">
                                    <div class="mb-3">
                                        <label class="form-label" for="expiry_period">{{ __('product.expires_in') }}</label>
                                        <div class="input-group">
                                            <input type="text" name="expiry_period" id="expiry_period"
                                                class="form-control input_number"
                                                value="{{ old('expiry_period', !empty($duplicate_product->expiry_period) ? @num_format($duplicate_product->expiry_period) : $expiry_period) }}"
                                                placeholder="{{ __('product.expiry_period') }}">
                                            <select name="expiry_period_type" id="expiry_period_type"
                                                class="form-select select2">
                                                <option value="months"
                                                    {{ old('expiry_period_type', !empty($duplicate_product->expiry_period_type) ? $duplicate_product->expiry_period_type : 'months') == 'months' ? 'selected' : '' }}>
                                                    {{ __('product.months') }}</option>
                                                <option value="days"
                                                    {{ old('expiry_period_type') == 'days' ? 'selected' : '' }}>
                                                    {{ __('product.days') }}</option>
                                                <option value="" {{ old('expiry_period_type') == '' ? 'selected' : '' }}>
                                                    {{ __('product.not_applicable') }}</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <br>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="enable_sr_no" value="1"
                                            id="enable_sr_no"
                                            {{ old('enable_sr_no', !empty($duplicate_product) ? $duplicate_product->enable_sr_no : false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="enable_sr_no">
                                            <strong>{{ __('lang_v1.enable_imei_or_sr_no') }}</strong>
                                        </label>
                                    </div>
                                    <small class="text-muted">{{ __('lang_v1.tooltip_sr_no') }}</small>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <br>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="not_for_selling" value="1"
                                            id="not_for_selling"
                                            {{ old('not_for_selling', !empty($duplicate_product) ? $duplicate_product->not_for_selling : false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="not_for_selling">
                                            <strong>{{ __('lang_v1.not_for_selling') }}</strong>
                                        </label>
                                    </div>
                                    <small class="text-muted">{{ __('lang_v1.tooltip_not_for_selling') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pricing & Tax -->
        <div class="row mt-3">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">{{ __('product.product_pricing') }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 mb-3">
                            <div class="col-md-4 @if (!$business->enable_price_tax) d-none @endif">
                                <div class="mb-3">
                                    <label class="form-label" for="tax">{{ __('product.applicable_tax') }}</label>
                                    <select name="tax" id="tax" class="form-control select2"
                                        @foreach ($tax_attributes as $k => $v) data-{{ $k }}="{{ $v }}" @endforeach>
                                        <option value="">{{ __('messages.please_select') }}</option>
                                        @foreach ($taxes as $id => $tax_name)
                                            <option value="{{ $id }}"
                                                {{ old('tax', !empty($duplicate_product->tax) ? $duplicate_product->tax : null) == $id ? 'selected' : '' }}>
                                                {{ $tax_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4 @if (!$business->enable_price_tax) d-none @endif">
                                <div class="mb-3">
                                    <label class="form-label" for="tax_type">{{ __('product.selling_price_tax_type') }} <span
                                            class="text-danger">*</span></label>
                                    <select name="tax_type" id="tax_type" class="form-control select2" required>
                                        <option value="inclusive"
                                            {{ old('tax_type', !empty($duplicate_product->tax_type) ? $duplicate_product->tax_type : 'exclusive') == 'inclusive' ? 'selected' : '' }}>
                                            {{ __('product.inclusive') }}</option>
                                        <option value="exclusive"
                                            {{ old('tax_type', !empty($duplicate_product->tax_type) ? $duplicate_product->tax_type : 'exclusive') == 'exclusive' ? 'selected' : '' }}>
                                            {{ __('product.exclusive') }}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label" for="type">{{ __('product.product_type') }} <span
                                            class="text-danger">*</span></label>
                                    <select name="type" id="type" class="form-control select2" required
                                        data-action="{{ !empty($duplicate_product) ? 'duplicate' : 'add' }}"
                                        data-product_id="{{ !empty($duplicate_product) ? $duplicate_product->id : '0' }}">
                                        @foreach ($product_types as $key => $type_name)
                                            <option value="{{ $key }}"
                                                {{ old('type', !empty($duplicate_product->type) ? $duplicate_product->type : null) == $key ? 'selected' : '' }}>
                                                {{ $type_name }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">{{ __('tooltip.product_type') }}</small>
                                </div>
                            </div>
                        </div>

                        <div id="product_form_part">
                            @include('products::product.partials.single_product_form_part', [
                                'business' => $business,
                                'profit_percent' => $default_profit_percent,
                            ])
                        </div>

                        <input type="hidden" id="variation_counter" value="1">
                        <input type="hidden" id="default_profit_percent" value="{{ $default_profit_percent }}">
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="row mt-3">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <input type="hidden" name="submit_type" id="submit_type">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('products.index') }}" class="btn btn-secondary">
                                <i class="fa fa-times"></i> {{ __('messages.cancel') }}
                            </a>

                            @if ($selling_price_group_count)
                                <button type="submit" value="submit_n_add_selling_prices"
                                    class="btn btn-warning submit_product_form">
                                    <i class="fa fa-tags"></i> {{ __('lang_v1.save_n_add_selling_price_group_prices') }}
                                </button>
                            @endif

                            @can('product.opening_stock')
                                <button id="opening_stock_button"
                                    @if (!empty($duplicate_product) && $duplicate_product->enable_stock == 0) disabled @endif type="submit"
                                    value="submit_n_add_opening_stock" class="btn btn-info submit_product_form">
                                    <i class="fa fa-cubes"></i> {{ __('lang_v1.save_n_add_opening_stock') }}
                                </button>
                            @endcan

                            <button type="submit" value="save_n_add_another" class="btn btn-success submit_product_form">
                                <i class="fa fa-plus-circle"></i> {{ __('lang_v1.save_n_add_another') }}
                            </button>

                            <button type="submit" value="submit" class="btn btn-primary submit_product_form">
                                <i class="fa fa-save"></i> {{ __('messages.save') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@section('javascript')
    @php $asset_v = env('APP_VERSION'); @endphp
    <script src="{{ asset('js/product.js?v=' . $asset_v) }}"></script>

    <script type="text/javascript">
        $(document).ready(function() {
            // Initialize select2
            $('.select2').select2();

            // Page leave confirmation
            let formChanged = false;
            $('#product_add_form input, #product_add_form select, #product_add_form textarea').on('change',
                function() {
                    formChanged = true;
                });

            window.addEventListener('beforeunload', function(e) {
                if (formChanged) {
                    e.preventDefault();
                    e.returnValue = '';
                }
            });

            $('#product_add_form').on('submit', function() {
                formChanged = false;
            });

            // Barcode scanner
            onScan.attachTo(document, {
                suffixKeyCodes: [13],
                reactToPaste: true,
                onScan: function(sCode, iQty) {
                    $('input#sku').val(sCode);
                },
                onScanError: function(oDebug) {
                    console.log(oDebug);
                },
                minLength: 2,
                ignoreIfFocusOn: ['input', '.form-control']
            });
        });
    </script>
@endsection