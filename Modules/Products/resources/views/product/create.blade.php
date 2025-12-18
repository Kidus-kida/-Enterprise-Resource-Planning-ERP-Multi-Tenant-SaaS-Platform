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
        <x-slot name="title">{{ __('Add new product') }}</x-slot>
        <ul class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('products.index') }}">{{ __('Products') }}</a>
            </li>
            <li class="breadcrumb-item active">{{ __('Add new product') }}</li>
        </ul>
    </x-breadcrumb>

    @php
        $form_class = empty($duplicate_product) ? 'create' : '';
        $is_image_required = !empty($common_settings['is_product_image_required']);
    @endphp

    <form action="{{ route('products.store') }}" method="POST" id="product_add_form"
        class="product_form {{ $form_class }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="submit_type" id="submit_type" value="">

        <!-- Basic Product Information -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">{{ __('Product Information') }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label" for="name">Product Name <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="name" id="name" class="form-control" required
                                        value="{{ old('name', !empty($duplicate_product->name) ? $duplicate_product->name : null) }}"
                                        placeholder="{{ __('Product Name') }}">
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label" for="sku">SKU</label>
                                    <input type="text" name="sku" id="sku" class="form-control"
                                        value="{{ old('sku') }}" placeholder="{{ __('SKU') }}">
                                    <small class="text-muted">{{ __('Unique product id or Stock Keeping Unit') }}</small>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label" for="barcode_type">Barcode Type <span
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
                                    <label class="form-label" for="semi_finished">Semi Finished <span
                                            class="text-danger">*</span></label>
                                    <select name="semi_finished" id="semi_finished" class="form-control" required>
                                        <option value="">Please Select</option>
                                        <option value="1" {{ old('semi_finished') == '1' ? 'selected' : '' }}>
                                            Yes
                                        </option>
                                        <option value="0" {{ old('semi_finished', '0') == '0' ? 'selected' : '' }}>
                                            No
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mt-2">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label" for="unit_id">Unit <span
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
                                    <label class="form-label" for="sub_unit_ids">{{ __('Related Sub Units') }}</label>
                                    <select name="sub_unit_ids[]" id="sub_unit_ids" class="form-control select2" multiple>
                                        @if (!empty($duplicate_product->sub_unit_ids))
                                            @foreach ($duplicate_product->sub_unit_ids as $sub_unit_id)
                                                <option value="{{ $sub_unit_id }}" selected>{{ $sub_unit_id }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <small class="text-muted">{{ __('Based on selected Unit it will show sub units for it. Select applicable sub-units. e.g. Unit = Case, Sub Units = Box, Packet') }}</small>
                                </div>
                            </div>

                            @if (!empty($common_settings['enable_secondary_unit']))
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label" for="secondary_unit_id">{{ __('Secondary Unit') }}</label>
                                        <select name="secondary_unit_id" id="secondary_unit_id" class="form-control select2">
                                            <option value="">Please Select</option>
                                            @foreach ($units as $id => $unit)
                                                <option value="{{ $id }}"
                                                    {{ old('secondary_unit_id', !empty($duplicate_product->secondary_unit_id) ? $duplicate_product->secondary_unit_id : null) == $id ? 'selected' : '' }}>
                                                    {{ $unit }}</option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted">{{ __('Allows user to enter product quantity in secondary unit during purchase/sell') }}</small>
                                    </div>
                                </div>
                            @endif

                            <div class="col-md-3 @if (!$business->enable_brand) d-none @endif">
                                <div class="mb-3">
                                    <label class="form-label" for="brand_id">Brand</label>
                                    <select name="brand_id" id="brand_id" class="form-control select2">
                                        <option value="">Please Select</option>
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
                                    <label class="form-label" for="category_id">Category</label>
                                    <select name="category_id" id="category_id" class="form-control select2">
                                        <option value="">Please Select</option>
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
                                    <label class="form-label" for="sub_category_id">Sub Category</label>
                                    <select name="sub_category_id" id="sub_category_id" class="form-control select2">
                                        <option value="">Please Select</option>
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
                                    <label class="form-label" for="product_locations">Business Locations</label>
                                    <select name="product_locations[]" id="product_locations" class="form-control select2"
                                        multiple>
                                        @foreach ($business_locations as $id => $location)
                                            <option value="{{ $id }}"
                                                {{ $default_location == $id ? 'selected' : '' }}>{{ $location }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">{{ __('Location where product will be available') }}</small>
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
                                            <strong>Manage Stock?</strong>
                                        </label>
                                    </div>
                                    <small class="text-muted"><i>{{ __('Enable stock management at product level') }}</i></small>
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
                                            <strong>VAT Input Claimed</strong>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3 @if (!empty($duplicate_product) && $duplicate_product->enable_stock == 0) d-none @endif"
                                id="alert_quantity_div">
                                <div class="mb-3">
                                    <label class="form-label" for="alert_quantity">Alert Quantity</label>
                                    <input type="text" name="alert_quantity" id="alert_quantity"
                                        class="form-control input_number" min="0"
                                        value="{{ old('alert_quantity', !empty($duplicate_product->alert_quantity) ? @format_quantity($duplicate_product->alert_quantity) : null) }}"
                                        placeholder="Alert Quantity">
                                    <small class="text-muted">{{ __('Get alert when product stock reaches or goes below the specified quantity') }}</small>
                                </div>
                            </div>

                            @if (!empty($common_settings['enable_product_warranty']))
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label" for="warranty_id">{{ __('Warranty') }}</label>
                                        <select name="warranty_id" id="warranty_id" class="form-control select2">
                                            <option value="">Please Select</option>
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
                                    <label class="form-label" for="stock_type">Stock Account</label>
                                    <select name="stock_type" id="stock_type" class="form-control select2">
                                        <option value="">Please Select</option>
                                        @foreach ($accounts as $id => $account)
                                            <option value="{{ $id }}">{{ $account }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label" for="product_description">{{ __('Description') }}</label>
                                    <textarea name="product_description" id="product_description" class="form-control"
                                        rows="3">{{ old('product_description', !empty($duplicate_product->product_description) ? $duplicate_product->product_description : null) }}</textarea>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label" for="upload_image">Product Image</label>
                                    <input type="file" name="image" id="upload_image" accept="image/*"
                                        {{ $is_image_required ? 'required' : '' }} class="form-control">
                                    <small class="text-muted">
                                        {{ __('Max file size: :sizeMB', ['size' => config('constants.document_size_limit') / 1000000]) }}
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
                        <h4 class="card-title mb-0">{{ __('Advanced Options') }}</h4>
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
                                        <label class="form-label" for="expiry_period">{{ __('Expires in') }}</label>
                                        <div class="input-group">
                                            <input type="text" name="expiry_period" id="expiry_period"
                                                class="form-control input_number"
                                                value="{{ old('expiry_period', !empty($duplicate_product->expiry_period) ? @num_format($duplicate_product->expiry_period) : $expiry_period) }}"
                                                placeholder="{{ __('Expiry Period') }}">
                                            <select name="expiry_period_type" id="expiry_period_type"
                                                class="form-select select2">
                                                <option value="months"
                                                    {{ old('expiry_period_type', !empty($duplicate_product->expiry_period_type) ? $duplicate_product->expiry_period_type : 'months') == 'months' ? 'selected' : '' }}>
                                                    {{ __('Months') }}</option>
                                                <option value="days"
                                                    {{ old('expiry_period_type') == 'days' ? 'selected' : '' }}>
                                                    {{ __('Days') }}</option>
                                                <option value="" {{ old('expiry_period_type') == '' ? 'selected' : '' }}>
                                                    {{ __('Not Applicable') }}</option>
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
                                            <strong>{{ __('Enable Product description, IMEI or Serial Number') }}</strong>
                                        </label>
                                    </div>
                                    <small class="text-muted">{{ __('Enable or disable adding product description, IMEI or Serial number while selling products in POS screen') }}</small>
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
                                            <strong>{{ __('Not for Selling') }}</strong>
                                        </label>
                                    </div>
                                    <small class="text-muted">{{ __('If checked, product will not be displayed in sales screen for selling purposes.') }}</small>
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
                        <h4 class="card-title mb-0">{{ __('Product Pricing') }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 mb-3">
                            <div class="col-md-4 @if (!$business->enable_price_tax) d-none @endif">
                                <div class="mb-3">
                                    <label class="form-label" for="tax">Applicable Tax</label>
                                    <select name="tax" id="tax" class="form-control select2">
                                        <option value="" data-rate="0">Please Select</option>
                                        @foreach ($taxes as $tax_id => $tax_name)
                                            @php
                                                // Get tax rate from TaxRate model
                                                $tax_rate = \App\TaxRate::find($tax_id);
                                                $rate = $tax_rate ? $tax_rate->amount : 0;
                                            @endphp
                                            <option value="{{ $tax_id }}" 
                                                data-rate="{{ $rate }}"
                                                {{ old('tax', !empty($duplicate_product->tax) ? $duplicate_product->tax : null) == $tax_id ? 'selected' : '' }}>
                                                {{ $tax_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4 @if (!$business->enable_price_tax) d-none @endif">
                                <div class="mb-3">
                                    <label class="form-label" for="tax_type">Selling Price Tax Type <span
                                            class="text-danger">*</span></label>
                                    <select name="tax_type" id="tax_type" class="form-control select2" required>
                                        <option value="inclusive"
                                            {{ old('tax_type', !empty($duplicate_product->tax_type) ? $duplicate_product->tax_type : 'exclusive') == 'inclusive' ? 'selected' : '' }}>
                                            {{ __('Inclusive') }}</option>
                                        <option value="exclusive"
                                            {{ old('tax_type', !empty($duplicate_product->tax_type) ? $duplicate_product->tax_type : 'exclusive') == 'exclusive' ? 'selected' : '' }}>
                                            {{ __('Exclusive') }}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label" for="type">Product Type <span
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
                                    <small class="text-muted">{{ __('Single product: Product with no variations. Variable product: Product with variations such as size, color, etc. Combo product: Combination of multiple products.') }}</small>
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
                                <i class="fa fa-times"></i> {{ __('Cancel') }}
                            </a>

                            @if ($selling_price_group_count)
                                <button type="submit" value="submit_n_add_selling_prices"
                                    class="btn btn-warning submit_product_form">
                                    <i class="fa fa-tags"></i> {{ __('Save & Add Selling-Price-Group Prices') }}
                                </button>
                            @endif

                            @can('product.opening_stock')
                                <button id="opening_stock_button"
                                    @if (!empty($duplicate_product) && $duplicate_product->enable_stock == 0) disabled @endif type="submit"
                                    value="submit_n_add_opening_stock" class="btn btn-info submit_product_form">
                                    <i class="fa fa-cubes"></i> {{ __('Save & Add Opening Stock') }}
                                </button>
                            @endcan

                            <button type="submit" value="save_n_add_another" class="btn btn-success submit_product_form">
                                <i class="fa fa-plus-circle"></i> Save & Add Another
                            </button>

                            <button type="submit" value="submit" class="btn btn-primary submit_product_form">
                                <i class="fa fa-save"></i> Save
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('page-scripts')
    <script>
    // Define LANG globally as early as possible
    window.LANG = {
        sure: "{{ __('Are you sure?') }}",
        sku_already_exists: "{{ __('product.sku_already_exists') }}",
        file_browse_label: "{{ __('Browse') }}",
        remove: "{{ __('Remove') }}",
        inc_tax: "{{ __('product.inc_of_tax') }}",
        exc_tax: "{{ __('product.exc_of_tax') }}",
        sp_inc_tax: "{{ __('product.selling_price_inc_tax') }}",
        sp_exc_tax: "{{ __('product.selling_price_exc_tax') }}"
    };

    window.addEventListener('load', function() {
        // Load dependencies in order
        if (typeof jQuery !== 'undefined') {
            loadProductScripts();
        }
    });

    function loadProductScripts() {
        var $ = jQuery;
        
        // Load accounting and helpers first
        $.getScript("{{ asset('js/accounting.min.js') }}", function() {
            $.getScript("{{ asset('js/helpers.js') }}", function() {
                $.getScript("{{ asset('js/product.js?v=' . env('APP_VERSION', '1')) }}", function() {
                    console.log('Product scripts loaded in order');
                    initializeProductForm();
                });
            });
        });
    }
    
    function initializeProductForm() {
        const $ = window.jQuery;
        
        $(document).ready(function() {
            // Initialize select2
            if ($.fn.select2) {
                $('.select2').select2({
                    width: '100%'
                });
            }

            // Handle enable_stock checkbox for Bootstrap 5
            $('#enable_stock').off('change').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#alert_quantity_div').removeClass('d-none').show();
                    $('#raw_material_div').removeClass('d-none').show();
                    $('#expiry_period_type').prop('disabled', false);
                    $('#opening_stock_button').prop('disabled', false);
                } else {
                    $('#alert_quantity_div').addClass('d-none').hide();
                    $('#raw_material_div').addClass('d-none').hide();
                    $('#expiry_period_type').val('').change().prop('disabled', true);
                    $('#opening_stock_button').prop('disabled', true);
                }
            });

            // Page leave confirmation
            let formChanged = false;
            $('#product_add_form').on('change', 'input, select, textarea', function() {
                formChanged = true;
            });

            window.addEventListener('beforeunload', function(e) {
                if (formChanged) {
                    e.preventDefault();
                    e.returnValue = '';
                }
            });

            // Robust Submit Handler
            // Use delegated event and catch it early
            $(document).on('click', '.submit_product_form', function(e) {
                var $btn = $(this);
                var $form = $('#product_add_form');
                
                console.log('Product save clicked. Type:', $btn.val());
                
                // Allow our handler to take priority
                $('#submit_type').val($btn.val());
                
                // If validation plugin is available and working, let it handle things
                if (typeof $form.valid === 'function') {
                    var isValid = $form.valid();
                    if (!isValid) {
                        console.warn('Form validation failed. Invalid fields:');
                        var validator = $form.validate();
                        $.each(validator.errorMap, function(key, value) {
                            console.warn('Field: ' + key + ' | Error: ' + value);
                        });
                        
                        // Scroll to the first error to help the user
                        var $firstError = $form.find('.error:visible').first();
                        if ($firstError.length) {
                            $('html, body').animate({
                                scrollTop: $firstError.offset().top - 100
                            }, 500);
                        }
                        return; 
                    }
                }
                
                // Force a standard submit 
                formChanged = false;
                console.log('Submitting form natively...');
                $form[0].submit();
                
                // Stop other handlers to prevent e.preventDefault in product.js from blocking us
                e.stopImmediatePropagation();
            });
            
            console.log('Product form initialization complete');
        });
    }
    </script>
@endpush