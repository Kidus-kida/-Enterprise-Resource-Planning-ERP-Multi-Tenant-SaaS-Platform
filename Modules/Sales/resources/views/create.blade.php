@extends('layouts.app')

@push('page-styles')
    <style>
        .product-search-container {
            position: relative;
        }

        .product-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #ced4da;
            max-height: 200px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
            list-style: none;
            margin-top: 2px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 0 0 0.375rem 0.375rem;
        }

        .product-results .product-item {
            padding: 8px 12px;
            cursor: pointer;
        }

        .product-results .product-item:hover {
            background-color: #f8f9fa;
            color: #007bff;
        }

        .product-search-input-group {
            display: flex;
            align-items: center;
            flex-direction: row;
        }
        
        .unit_text {
            font-size: 0.85em;
            color: #6c757d;
            margin-top: 4px;
            display: block;
        }
    </style>
@endpush

@section('page-content')
    <div class="content container-fluid">

        <!-- Page Header -->
        <x-breadcrumb class="col">
            <x-slot name="title">{{ __('Add Sales') }}</x-slot>
            <ul class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('sales.index') }}">{{ __('Sales') }}</a>
                </li>
                <li class="breadcrumb-item active">
                    {{ __('Add Sales') }}
                </li>
            </ul>
        </x-breadcrumb>

        <form action="{{ route('sales.store') }}" method="post" enctype="multipart/form-data" id="sales_form">
            @csrf
            <div class="row">
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="input-block mb-3">
                                <x-form.label>{{ __('Invoice No') }}</x-form.label>
                                <x-form.input type="text" name="invoice_no" value="{{ $invoice_no }}" disabled />
                                <input type="hidden" name="invoice_no" value="{{ $invoice_no }}">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="input-block mb-3">
                                <x-form.label>{{ __('Customer') }} <span class="text-danger">*</span></x-form.label>
                                <div class="position-relative">
                                    <x-form.select id="customerSelect" name="contact_id" class="form-control pe-5" required>
                                        <option value="" disabled selected>-- {{ __('Select Customer') }} --</option>
                                        @foreach ($customers as $key => $customer)
                                            <option value="{{ $key }}">{{ $customer }}</option>
                                        @endforeach
                                    </x-form.select>
                                    <button type="button"
                                        class="btn btn-outline-primary position-absolute end-0 top-0 d-flex align-items-center px-3"
                                        data-url="{{ route('contacts.create') }}" data-ajax-modal="true" data-size="lg"
                                        data-title="Add Customer" style="height: 44px; border-radius: 0 .375rem .375rem 0;">
                                        +
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="input-block mb-3">
                                <x-form.label>{{ __('Ref No') }}</x-form.label>
                                <x-form.input type="text" name="ref_no" placeholder="{{ __('Reference Number') }}" />
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="input-block mb-3">
                                <x-form.label>{{ __('Sales Date') }} <span class="text-danger">*</span></x-form.label>
                                <x-form.input type="date" name="transaction_date" value="{{ date('Y-m-d') }}" required />
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="input-block mb-3">
                                <x-form.label>{{ __('Status') }} <span class="text-danger">*</span></x-form.label>
                                <x-form.select name="status" class="form-control" required>
                                    <option value="final">{{ __('Final') }}</option>
                                    <option value="draft">{{ __('Draft') }}</option>
                                    <option value="proforma">{{ __('Proforma') }}</option>
                                </x-form.select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="input-block mb-3">
                                <x-form.label>{{ __('VAT Invoice') }}</x-form.label>
                                <x-form.select name="is_vat" class="form-control">
                                    <option value="1">{{ __('Yes') }}</option>
                                    <option value="0" selected>{{ __('No') }}</option>
                                </x-form.select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="input-block mb-3">
                                <x-form.label>{{ __('Business Location') }} <span class="text-danger">*</span></x-form.label>
                                <x-form.select name="location_id" id="location_id" class="form-control" required>
                                    <option value="" disabled selected>-- {{ __('Select Location') }} --</option>
                                    @foreach ($business_locations as $key => $location)
                                        <option value="{{ $key }}">{{ $location }}</option>
                                    @endforeach
                                </x-form.select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="input-block mb-3">
                                <x-form.label>{{ __('Store') }} <span class="text-danger">*</span></x-form.label>
                                <x-form.select name="store_id" id="store_id" class="form-control" required disabled>
                                    <option value="" disabled selected>-- {{ __('Select Store') }} --</option>
                                </x-form.select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="input-block mb-3">
                                <x-form.label>{{ __('Pay term') }}</x-form.label>
                                <div class="d-flex align-items-center gap-0">
                                    <x-form.input type="number" name="pay_term_number" min="1" placeholder="e.g., 30" />
                                    <x-form.select name="pay_term_type">
                                        <option value="days">{{ __('Days') }}</option>
                                        <option value="months">{{ __('Months') }}</option>
                                    </x-form.select>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="input-block mb-3">
                                <x-form.label>{{ __('Attach Document') }}</x-form.label>
                                <x-form.input type="file" name="document" />
                            </div>
                        </div>

                        <div class="form-group mb-4 product-search-container">
                            <x-form.label>{{ __('Product / Service') }}</x-form.label>
                            <div class="product-search-input-group">
                                <input type="text" id="product-filter" class="form-control"
                                    placeholder="{{ __('Search product by name, SKU or barcode...') }}" disabled>
                                <button type="button" class="btn btn-outline-primary d-flex align-items-center py-2 px-3"
                                    style="border-radius: 0 .375rem .375rem 0;" data-bs-toggle="modal" data-bs-target="#addProductModal">
                                    <i class="fa fa-plus"></i> {{ __('Add') }}
                                </button>
                            </div>
                            <div id="product-results" class="product-results"></div>
                        </div>

                        <div class="table-responsive mb-4">
                            <table class="table table-bordered" id="sales_table">
                                <thead class="bg-light">
                                    <tr>
                                        <th style="width: 50px">#</th>
                                        <th>{{ __('Product') }}</th>
                                        <th style="width: 130px">{{ __('Quantity') }}</th>
                                        <th style="width: 130px">{{ __('Available Qty') }}</th>
                                        <th style="width: 160px">{{ __('Unit Price') }}</th>
                                        <th style="width: 140px">{{ __('Discount %') }}</th>
                                        <th style="width: 160px">{{ __('Subtotal') }}</th>
                                        <th>{{ __('Tax') }}</th>
                                        <th style="width: 160px">{{ __('Line Total') }}</th>
                                        <th style="width: 50px">{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody id="product-table-body">
                                    <tr id="no-items-row">
                                        <td colspan="9" class="text-center py-4 text-muted">
                                            <i class="fas fa-box-open fa-2x mb-2"></i>
                                            <br>
                                            {{ __('No items added yet. Search and select products above to add them.') }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="row mb-2">
                            <div class="col-md-6"></div>
                            <div class="col-md-6 text-end">
                                <div class="row align-items-center mb-2">
                                    <div class="col-md-7 text-end fw-bold">{{ __('Subtotal:') }}</div>
                                    <div class="col-md-5 text-end">
                                        <span id="display-grand-total">0.00</span>
                                        <input type="hidden" name="total_before_tax" id="total_before_tax" value="0">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-md-6"></div>
                            <div class="col-md-6">
                                <div class="row align-items-center mb-2">
                                    <div class="col-md-7 text-end fw-bold">{{ __('Discount:') }}</div>
                                    <div class="col-md-5">
                                        <div class="input-group input-group-sm">
                                            <select class="form-select" name="discount_type" id="discount_type" style="max-width: 80px;">
                                                <option value="fixed">{{ __('Fixed') }}</option>
                                                <option value="percentage">{{ __('%') }}</option>
                                            </select>
                                            <input type="number" class="form-control text-end" name="discount_amount" id="discount_amount" value="0" step="0.01">
                                        </div>
                                        <span class="d-none" id="display-discount-total">0.00</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-md-6"></div>
                            <div class="col-md-6">
                                <div class="row align-items-center mb-2">
                                    <div class="col-md-7 text-end fw-bold">{{ __('Order Tax:') }}</div>
                                    <div class="col-md-5">
                                        <select name="tax_rate_id" id="tax_rate_id" class="form-select form-select-sm text-end">
                                            <option value="" data-tax-amount="0">{{ __('None') }}</option>
                                            @foreach($taxes as $tax)
                                                <option value="{{ $tax->id }}" data-tax-amount="{{ $tax->amount }}">{{ $tax->name }}</option>
                                            @endforeach
                                        </select>
                                        <input type="hidden" name="tax_amount" id="tax_amount" value="0">
                                        <span class="d-block text-end small text-muted mt-1" id="display-tax-total">0.00</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-md-6"></div>
                            <div class="col-md-6">
                                <div class="row align-items-center mb-2">
                                    <div class="col-md-7 text-end fw-bold">{{ __('Shipping:') }}</div>
                                    <div class="col-md-5">
                                        <input type="number" name="shipping_charges" class="form-control form-control-sm text-end" id="shipping_charges" value="0.00" step="0.01">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6"></div>
                            <div class="col-md-6 text-end">
                                <h4 class="mb-0 text-primary">
                                    <strong>{{ __('Final Total:') }} <span id="display-final-total">0.00</span></strong>
                                    <input type="hidden" name="final_total" id="final_total" value="0">
                                </h4>
                            </div>
                        </div>
                        <hr />

                        <div class="col-md-12">
                            <div class="input-block mb-3">
                                <x-form.label>{{ __('Sales Note') }}</x-form.label>
                                <x-form.textarea name="sale_note" rows="3" placeholder="{{ __('Any notes for this sale...') }}" />
                            </div>
                        </div>

                        <!-- Payment Methods Section -->
                        <div class="col-md-12 mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0">{{ __('Payment Details') }}</h5>
                                <div class="text-end">
                                    <span class="badge bg-light text-muted border me-2">{{ __('Total Paid') }}: <span id="total_paid">0.00</span></span>
                                    <span class="badge bg-light text-danger border">{{ __('Payment Due') }}: <span id="payment_due">0.00</span></span>
                                </div>
                            </div>

                            <div id="payment_rows_div">
                                @include('sales::partials.payment_row', [
                                    'row_index' => 0,
                                    'payment_types' => $payment_types,
                                    'accounts' => $accounts
                                ])
                            </div>

                            <button type="button" class="btn btn-primary btn-block mt-3" id="add-payment-row">
                                <i class="fa fa-plus"></i> {{ __('Add Payment Method') }}
                            </button>
                        </div>
                        <!-- End Payment Methods -->

                    </div>
                </div>
            </div>
            
            <div class="submit-section text-end mb-5">
                <button type="reset" class="btn btn-outline-secondary px-4 me-2">{{ __('Reset') }}</button>
                <button type="submit" class="btn btn-primary submit-btn px-5">{{ __('Save Sale') }}</button>
            </div>
        </form>
    </div>
@endsection

@push('page-scripts')
    {{-- Include jQuery if not already loaded --}}
    @if (!isset($jquery_loaded))
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        @php $jquery_loaded = true; @endphp
    @endif

    <script>
        $(document).ready(function() {
            let row_count = 0;

            // ===== ENABLE/DISABLE PRODUCT SEARCH BASED ON CUSTOMER SELECTION =====
            const customerSelect = $('#customerSelect');
            const productFilter = $('#product-filter');

            customerSelect.on('change', function() {
                if ($(this).val()) {
                    productFilter.prop('disabled', false).focus();
                } else {
                    productFilter.prop('disabled', true);
                }
            });

            // ===== STORE DROPDOWN AJAX (Location → Store) =====
            const locationSelect = $('#location_id');
            const storeSelect = $('#store_id');

            locationSelect.on('change', function() {
                const locationId = $(this).val();
                storeSelect.empty().prop('disabled', true).append('<option>Loading...</option>');

                if (locationId) {
                    $.ajax({
                        url: "{{ route('sales.stores.by.location', ['locationId' => '__ID__']) }}".replace('__ID__', locationId),
                        type: 'GET',
                        dataType: 'json',
                        success: function(data) {
                            storeSelect.empty();
                            storeSelect.append('<option value="" disabled selected>-- {{ __("Select Store") }} --</option>');
                            if (data && Object.keys(data).length > 0) {
                                $.each(data, function(id, name) {
                                    storeSelect.append(new Option(name, id));
                                });
                                storeSelect.prop('disabled', false);
                            } else {
                                storeSelect.append('<option disabled>{{ __("No stores found") }}</option>');
                            }
                        },
                        error: function() {
                            storeSelect.empty().append('<option disabled>{{ __("Error loading stores") }}</option>');
                        }
                    });
                }
            });

            storeSelect.on('change', function() {
                if ($(this).val()) {
                    $('#product-filter').prop('disabled', false).focus();
                } else {
                    $('#product-filter').prop('disabled', true);
                }
            });

            // ===== PRODUCT SEARCH =====
            let debounceTimeout;
            $('#product-filter').on('input', function() {
                const term = $(this).val();
                if (term.length < 2) {
                    $('#product-results').hide();
                    return;
                }

                clearTimeout(debounceTimeout);
                debounceTimeout = setTimeout(() => {
                    $.ajax({
                        url: "{{ route('sales.get_products') }}",
                        method: 'GET',
                        data: { 
                            term: term,
                            location_id: $('#location_id').val(),
                            store_id: $('#store_id').val()
                        },
                        success: function(data) {
                            let resultsHtml = '';
                            if (data.length > 0) {
                                data.forEach(item => {
                                    resultsHtml += `<div class="product-item" data-product-id="${item.product_id}" data-variation-id="${item.variation_id}">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>${item.text}</strong><br>
                                                <small class="text-muted">${item.sub_sku || ''}</small>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge bg-soft-primary text-primary px-2">Price: ${parseFloat(item.selling_price).toFixed(2)}</span><br>
                                                <small class="text-muted">Stock: ${parseFloat(item.current_stock).toFixed(2)} ${item.unit || ''}</small>
                                            </div>
                                        </div>
                                    </div>`;
                                });
                            } else {
                                resultsHtml = `<div class="p-3 text-center text-muted">{{ __("No products found") }}</div>`;
                            }
                            $('#product-results').html(resultsHtml).show();
                        }
                    });
                }, 300);
            });

            $(document).on('click', '.product-item', function() {
                const productId = $(this).data('product-id');
                const variationId = $(this).data('variation-id');
                addProductRow(productId, variationId);
                $('#product-filter').val('');
                $('#product-results').hide();
            });

            function addProductRow(productId, variationId) {
                // Check if already exists
                let exists = false;
                $('.hidden_variation_id').each(function() {
                    if ($(this).val() == variationId) {
                        exists = true;
                        return false;
                    }
                });
                
                if (exists) {
                    alert("{{ __('Product already added to list') }}");
                    return;
                }

                $.ajax({
                    url: "{{ route('sales.get_sell_entry_row') }}",
                    method: 'POST',
                    data: {
                        product_id: productId,
                        variation_id: variationId,
                        row_count: row_count,
                        location_id: $('#location_id').val(),
                        store_id: $('#store_id').val(),
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(html) {
                        $('#no-items-row').hide();
                        $('#product-table-body').append(html);
                        row_count++;
                        updateSerialNumbers();
                        calculateTotal();
                    },
                    error: function() {
                        alert("{{ __('Error adding product row') }}");
                    }
                });
            }

            // ===== CALCULATIONS =====
            $(document).on('input change', '.sell_quantity, .sell_unit_price, .sell_line_discount, .sell_line_tax_id, #shipping_charges, #discount_amount, #discount_type, #tax_rate_id', function() {
                calculateTotal();
            });

            function calculateTotal() {
                let totalLineTotal = 0; // Sum of line totals (including line tax)
                let totalBeforeTax = 0; // Sum of line quantities * unit price (subtotal)

                $('#product-table-body tr.sell_entry_row').each(function() {
                    const row = $(this);
                    const qty = parseFloat(row.find('.sell_quantity').val()) || 0;
                    const price = parseFloat(row.find('.sell_unit_price').val()) || 0;
                    const discPercent = parseFloat(row.find('.sell_line_discount').val()) || 0;
                    const lineTaxAmount = parseFloat(row.find('.sell_line_tax_id option:selected').data('tax_amount')) || 0; // Line tax rate

                    const totalWithoutDiscount = qty * price; 
                    const discountValue = (totalWithoutDiscount * discPercent) / 100;
                    const totalAfterDiscount = totalWithoutDiscount - discountValue;
                    
                    const taxValue = (totalAfterDiscount * lineTaxAmount) / 100;
                    const lineTotal = totalAfterDiscount + taxValue; // Line total (inc line tax)

                    totalBeforeTax += totalAfterDiscount; // Logic: Subtotal is usually post-line-discount
                    totalLineTotal += lineTotal;

                    // Update row displays
                    row.find('.row_subtotal_before_tax').text(totalAfterDiscount.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                    row.find('.row_subtotal_before_tax_hidden').val(totalAfterDiscount.toFixed(2));
                    
                    // Calc individual unit price inc tax for backend (approx)
                    const unitPriceIncTax = (lineTotal / qty) || 0;
                    row.find('.sell_line_unit_price_inc_tax').val(unitPriceIncTax.toFixed(4));
                    
                    row.find('.sell_line_tax_amount').val(taxValue.toFixed(4));
                    row.find('.row_line_total').text(lineTotal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                    row.find('.row_line_total_hidden').val(lineTotal.toFixed(2));
                });

                // Order Level Calculations
                let orderDiscountAmount = 0;
                const discountType = $('#discount_type').val();
                const discountVal = parseFloat($('#discount_amount').val()) || 0;

                if (discountType === 'fixed') {
                    orderDiscountAmount = discountVal;
                } else {
                    orderDiscountAmount = (totalBeforeTax * discountVal) / 100;
                }

                // Order Tax
                let orderTaxAmount = 0;
                const orderTaxRate = parseFloat($('#tax_rate_id option:selected').data('tax-amount')) || 0;
                
                // Taxable amount = Subtotal - Order Discount
                let taxableAmount = totalBeforeTax - orderDiscountAmount;
                if(taxableAmount < 0) taxableAmount = 0;

                orderTaxAmount = (taxableAmount * orderTaxRate) / 100;

                const shipping = parseFloat($('#shipping_charges').val()) || 0;
                
                // Final Total = (Sum of Line Totals) - Order Discount + Order Tax + Shipping
                // Note: Line Totals already include Line Tax.
                // If using Order Tax, usually Line Tax should be 0 or they stack. 
                // We will add Order Tax on top of everything.
                 
                // Wait, if Line Tax is used, totalBeforeTax excludes it?
                // totalBeforeTax calculated above IS (Price * Qty - Line Discount). 
                // So it excludes Line Tax.
                
                // If we want Subtotal displayed, usually it's totalBeforeTax.
                
                const finalTotal = totalBeforeTax - orderDiscountAmount + orderTaxAmount + shipping; 
                // Note: This ignores Line Tax in the Final Total calculation if we assume "Order Tax" replaces it or stacks on Subtotal.
                // But wait, the loop calculated `lineTotal` which INCLUDES Line Tax.
                // If we have line taxes, they should be in the final total.
                // Let's use `totalLineTotal` (which includes line tax) - OrderDiscount + OrderTax + Shipping.
                // Careful: Order Tax is usually on (Subtotal - OrderDiscount). Subtotal usually EXCLUDES line tax.
                
                // Adjusted logic:
                // Final = (Sum of Line Totals) - OrderDiscount + OrderTax + Shipping.
                // Provided OrderDiscount applies to the "Base" price. 
                // This is getting complex. Let's stick to a standard:
                // Subtotal = Sum(Line Total Excl Tax)
                // Order Discount = applied on Subtotal
                // Order Tax = applied on (Subtotal - Order Discount)
                // Final = (Subtotal - Order Discount) + Order Tax + Sum(Line Taxes) + Shipping
                
                // Let's re-sum:
                let subtotalExclTax = totalBeforeTax; // (Qty * Price - Line Disc)
                let sumLineTaxes = 0;
                $('#product-table-body tr.sell_entry_row').each(function() {
                     sumLineTaxes += parseFloat($(this).find('.sell_line_tax_amount').val()) || 0;
                });
                
                // Recalculate Order Tax on (Subtotal - Order Discount)
                taxableAmount = subtotalExclTax - orderDiscountAmount;
                orderTaxAmount = (taxableAmount * orderTaxRate) / 100;

                const grandTotal = (subtotalExclTax - orderDiscountAmount) + sumLineTaxes + orderTaxAmount + shipping;

                // Update Summary displays
                $('#display-grand-total').text(subtotalExclTax.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                $('#total_before_tax').val(subtotalExclTax.toFixed(2));
                
                // Display Order Tax
                $('#display-tax-total').text(orderTaxAmount.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                $('#tax_amount').val(orderTaxAmount.toFixed(2)); 
                
                $('#display-final-total').text(grandTotal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                $('#final_total').val(grandTotal.toFixed(2));

                calculatePaymentDue();
            }

            function updateSerialNumbers() {
                $('.sr_number').each(function(index) {
                    $(this).text(index + 1);
                });
            }

            $(document).on('click', '.remove_sell_entry_row', function() {
                $(this).closest('tr').remove();
                if ($('#product-table-body tr.sell_entry_row').length === 0) {
                    $('#no-items-row').show();
                }
                updateSerialNumbers();
                calculateTotal();
            });

            // ===== PAYMENTS =====
            $(document).on('click', '#add-payment-row', function() {
                const count = $('.payment_row').length;
                $.ajax({
                    url: "{{ route('sales.get_payment_row') }}",
                    method: 'POST',
                    data: {
                        row_index: count,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(html) {
                        $('#payment_rows_div').append(html);
                        calculatePaymentDue();
                    }
                });
            });

            $(document).on('click', '.remove_payment_row', function() {
                $(this).closest('.payment_row').next('hr').remove();
                $(this).closest('.payment_row').remove();
                calculatePaymentDue();
            });

            $(document).on('change', '.payment_method', function() {
                const method = $(this).val();
                const row = $(this).closest('.payment_row');
                const methodFields = row.find('.method_fields');
                
                row.find('.cheque_fields, .card_fields').addClass('hide').hide();
                methodFields.addClass('hide').hide();

                if (method === 'cheque') {
                    methodFields.removeClass('hide').show();
                    row.find('.cheque_fields').removeClass('hide').show();
                } else if (method === 'card') {
                    methodFields.removeClass('hide').show();
                    row.find('.card_fields').removeClass('hide').show();
                } else if (method === 'credit_sale') {
                    row.find('.payment_amount').val(0).trigger('change');
                }
            });

            $(document).on('input change', '.payment_amount', function() {
                calculatePaymentDue();
            });

            function calculatePaymentDue() {
                const finalTotal = parseFloat($('#final_total').val()) || 0;
                let paid = 0;
                $('.payment_amount').each(function() {
                    paid += parseFloat($(this).val()) || 0;
                });
                const due = finalTotal - paid;
                
                $('#total_paid').text(paid.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                $('#payment_due').text(due.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                
                if (due > 0) {
                    $('#payment_due').removeClass('text-success').addClass('text-danger');
                } else {
                    $('#payment_due').removeClass('text-danger').addClass('text-success');
                }
            }

            // Close search results when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.product-search-container').length) {
                    $('#product-results').hide();
                }
            });

            // Form Submit Loading State
            $('#sales_form').on('submit', function() {
                const btn = $(this).find('button[type="submit"]');
                btn.prop('disabled', true);
                btn.html('<span class="spinner-border spinner-border-sm me-2"></span> {{ __("Processing...") }}');
            });

            // Initial focus if customer already selected (e.g. on back)
            if (customerSelect.val()) {
                productFilter.prop('disabled', false);
            }
        });
    </script>
@endpush
