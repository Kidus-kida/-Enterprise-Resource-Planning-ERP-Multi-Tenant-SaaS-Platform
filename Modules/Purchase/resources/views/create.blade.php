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
    </style>
@endpush

@section('page-content')
    <div class="content container-fluid">

        <!-- Page Header -->
        <x-breadcrumb class="col">
            <x-slot name="title">{{ __('Purchase') }}</x-slot>
            <ul class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
                </li>
                <li class="breadcrumb-item active">
                    {{ __('Purchase') }}
                </li>
            </ul>
        </x-breadcrumb>


        <form action="{{ route('purchase.store') }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="input-block mb-3">
                                <x-form.label>{{ __('Purchase No') }}</x-form.label>
                                <x-form.input type="text" name="invoice_no" value="{{ $purchase_no }}" disabled />
                                <input type="hidden" name="invoice_no" value="{{ $purchase_no }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="input-block mb-3">
                                <x-form.label>{{ __('Supplier') }}</x-form.label>

                                <div class="position-relative">
                                    <x-form.select id="supplierSelect" name="contact_id" class="form-control pe-5">
                                        <option value="" disabled selected>-- Select Supplier --</option>
                                        @foreach ($suppliers ?? [] as $key => $supplier)
                                            <option value="{{ $key }}">{{ $supplier }}</option>
                                        @endforeach
                                    </x-form.select>

                                    <!-- Overlapping plus button -->
                                    <button type="button"
                                        class="btn btn-outline-primary position-absolute end-0 top-0 d-flex align-items-center px-3"
                                        data-url="{{ route('supplier.create') }}" data-ajax-modal="true" data-size="lg"
                                        data-title="Add Supplier" style="height: 44px; border-radius: 0 .375rem .375rem 0;">
                                        +
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="input-block mb-3">
                                <x-form.label>{{ __('P. Invoice No') }}</x-form.label>
                                <x-form.input type="text" name="ref_no" />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="input-block mb-3">
                                <x-form.label>{{ __('Received Date') }}</x-form.label>
                                <x-form.input type="date" name="transaction_date" />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="input-block mb-3">
                                <x-form.label>{{ __('Invoice Date') }}</x-form.label>
                                <x-form.input type="date" name="invoice_date" />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="input-block mb-3">
                                <x-form.label>{{ __('Purchase Status') }}</x-form.label>
                                <x-form.select name="status">
                                    <option value="" disabled selected>-- Select Status --</option>
                                    @foreach($orderStatuses as $key => $value)
                                        <option value="{{ $key }}">{{ $value }}</option>
                                    @endforeach
                                </x-form.select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="input-block mb-3">
                                <x-form.label>{{ __('VAT Invoice') }}</x-form.label>
                                <x-form.select name="is_vat">
                                    <option value="" disabled selected>-- Select --</option>
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </x-form.select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="input-block mb-3">
                                <x-form.label>{{ __('Business Location') }}</x-form.label>
                                <x-form.select name="location_id" id="location_id">
                                    <option value="" disabled selected>-- Select Location --</option>
                                    @foreach ($business_locations ?? [] as $key => $location)
                                        <option value="{{ $key }}">{{ $location }}</option>
                                    @endforeach
                                </x-form.select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="input-block mb-3">
                                <x-form.label>{{ __('Store') }}</x-form.label>
                                <x-form.select name="store_id" id="store_id" disabled>
                                    <option value="" disabled selected>-- Select Store --</option>
                                    {{-- Options will be loaded via AJAX --}}
                                </x-form.select>
                            </div>
                        </div>

                        <div class="col-sm-4">
                            <x-form.input-block>
                                <x-form.label>{{ __('Pay term') }}</x-form.label>
                                <div class="d-flex align-items-center gap-0">
                                    <x-form.input type="number" name="pay_term_number" min="1"
                                        placeholder="Enter pay term" />
                                    <x-form.select name="pay_term_type">
                                        <option value="" disabled selected>-- Unit --</option>
                                        <option value="months">Months</option>
                                        <option value="days">Days</option>
                                    </x-form.select>
                                </div>
                            </x-form.input-block>
                        </div>

                        <div class="col-sm-8">
                            <x-form.label class="col-form-label">{{ __('Attach Document') }}</x-form.label>
                            <x-form.input type="file" name="document" />
                        </div>

                        <div class="form-group mb-4 product-search-container">
                            <x-form.label>{{ __('Product / Service') }}</x-form.label>
                            <div class="product-search-input-group">
                                <!-- Search box -->
                                <input type="text" id="product-filter" class="form-control"
                                    placeholder="Search product by name or code">

                                <button type="button" class="btn btn-outline-primary d-flex align-items-center py-2 px-3"
                                    style="border-radius: 0 .375rem .375rem 0;" data-bs-toggle="modal"
                                    data-bs-target="#addProductModal">
                                    <i class="fa fa-plus"></i> {{ __('Add') }}
                                </button>
                            </div>
                            <!-- Native select (scrollable list) -->
                            <div id="product-results" class="product-results"></div>
                        </div>



                        <div class="table-responsive mb-4">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('Product Name') }}</th>
                                        <th>{{ __('Purchase Quantity') }}</th>
                                        <th>{{ __('Available Qty') }}</th>
                                        <th>{{ __('Unit Cost') }} <br> {{ __('(Before Discount)') }}</th>
                                        <th>{{ __('Discount Percent') }}</th>
                                        <th>{{ __('Unit Cost') }} <br> {{ __('(Before Tax)') }}</th>
                                        <th>{{ __('Subtotal') }} <br> {{ __('(Before Tax)') }}</th>
                                        <th>{{ __('Product Tax') }}</th>
                                        <th>{{ __('Net Cost') }}</th>
                                        <th>{{ __('Line Total') }}</th>
                                        <th>{{ __('Profit Margin %') }}</th>
                                        <th>{{ __('Unit Selling Price (Exc. tax)') }}</th>
                                        <th>{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody id="product-table-body">
                                    <!-- Placeholder row when no items exist -->
                                    <tr id="no-items-row">
                                        <td colspan="14" class="text-center py-4 text-muted">
                                            <i class="fas fa-box-open fa-2x mb-2"></i>
                                            <br>
                                            {{ __('No items added yet. Search and select products above to add them.') }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <hr />
                        <div class="row mb-3">
                            <div class="col-md-6"></div>
                            <div class="col-md-6 text-end">
                                <h5 class="mb-0">
                                    <strong>{{ __('Purchase Total:') }} <span
                                            id="display-grand-total">0.00</span></strong>
                                    <input type="hidden" id="display-grand-total" value=0 name="total_before_tax">
                                </h5>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6"></div>
                            <div class="col-md-6 text-end">
                                <h5 class="mb-0">
                                    <strong>{{ __('Discount:') }} <span id="display-discount-total">0.00</span></strong>
                                    <input type="hidden" id="discount_amount" value=0 name="discount_amount">
                                </h5>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6"></div>
                            <div class="col-md-6 text-end">
                                <h5 class="mb-0">
                                    <strong>{{ __('Net Total:') }} <span id="display-final-total">0.00</span></strong>
                                    <input type="hidden" id="final_total" value=0 name="final_total">
                                </h5>
                            </div>
                        </div>
                        <hr />

                        <div class="col-md-12">
                            <div class="input-block mb-3">
                                <x-form.label>{{ __('Additional note') }}</x-form.label>
                                <x-form.textarea name="additional_notes" />
                            </div>
                        </div>

                        <!-- Payment Methods Section -->
                        <div class="col-md-12 mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0">{{ __('Payment Details') }}</h5>
                            </div>

                            <div id="payment_rows_div">
                                {{-- Initial payment row --}}
                                @include('purchase::partials.payment_row', [
                                    'row_index' => 0,
                                    'payment_types' => $payment_types ?? [],
                                    'accounts' => $accounts ?? [],
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
            <div class="submit-section">
                <x-form.button class="btn btn-primary submit-btn">{{ __('Submit') }}</x-form.button>
            </div>
        </form>
    </div>
@endsection

@push('page-script')
    {{-- Include jQuery if not already loaded --}}
    @if (!isset($jquery_loaded))
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        @php $jquery_loaded = true; @endphp
    @endif

    {{-- Purchase creation JavaScript --}}
    <script src="{{ asset('modules/purchase/assets/js/purchase.js?v=' . time()) }}"></script>

    <script>
        $(document).ready(function() {
            // ===== STORE DROPDOWN AJAX (Location → Store) =====
            const locationSelect = $('#location_id');
            const storeSelect = $('#store_id');

            storeSelect.prop('disabled', true);

            locationSelect.on('change', function() {
                const locationId = $(this).val();
                storeSelect.empty().prop('disabled', true).append('<option>Loading...</option>');

                if (locationId) {
                    $.ajax({
                        url: "{{ route('purchase.stores.by.location', ['locationId' => '__ID__']) }}"
                            .replace('__ID__', locationId),
                        type: 'GET',
                        dataType: 'json',
                        success: function(data) {
                            storeSelect.empty();
                            storeSelect.append(
                                '<option value="" disabled selected>-- Select Store --</option>'
                                );
                            if (data && Object.keys(data).length > 0) {
                                $.each(data, function(id, name) {
                                    storeSelect.append(new Option(name, id));
                                });
                                storeSelect.prop('disabled', false);
                            } else {
                                storeSelect.append('<option disabled>No stores found</option>');
                            }
                        },
                        error: function() {
                            storeSelect.empty().append(
                                '<option disabled>Error loading stores</option>');
                        }
                    });
                }
            });

            // ===== PRODUCT SEARCH & SELECTION =====
            const productFilter = $('#product-filter');
            const productResults = $('#product-results');
            let selectedProduct = null;

            // Close results when clicking elsewhere
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.product-search-container').length) {
                    productResults.hide();
                }
            });

            // Fetch products on input
            productFilter.on('input', function() {
                const term = $(this).val().trim();
                const supplierId = $('#supplierSelect').val();

                if (term.length < 2 || !supplierId) {
                    productResults.hide();
                    return;
                }
                $.ajax({
                    url: "{{ route('purchase.getProductsPurchases') }}",
                    method: 'GET',
                    data: {
                        term: term,
                        supplier_id: supplierId
                    },
                    success: function(products) {
                        productResults.empty();
                        if (products.length > 0) {
                            products.forEach(p => {
                                // Make sure p has: product_id, variation_id, text
                                const item = $(`
                <div class="product-item"
                    data-product-id="${p.product_id}"
                    data-variation-id="${p.variation_id}"
                    data-name="${p.text}">
                    ${p.text}
                </div>
            `);
                                productResults.append(item);
                            });
                            productResults.show();
                        } else {
                            productResults.hide();
                        }
                    }
                });
            });

            // Handle product selection
            $(document).on('click', '.product-item', function() {
                const productId = $(this).data('product-id');
                const variationId = $(this).data('variation-id');

                if (productId && variationId) {
                    // Use the existing function from purchase.js
                    get_purchase_entry_row(productId, variationId);

                    // Clear search
                    $('#product-filter').val('');
                    $('#product-results').hide();
                }
            });

            function get_purchase_entry_row(product_id, variation_id) {
                if (product_id) {
                    let duplicate_found = false;
                    // Fix: Target the correct table body
                    $('#product-table-body')
                        .find('tr')
                        .each(function() {
                            const hiddenVariationId = $(this).find('.hidden_variation_id').val();
                            if (hiddenVariationId && parseInt(hiddenVariationId) === parseInt(variation_id)) {
                                duplicate_found = true;
                            }
                        });

                    if (duplicate_found) {
                        alert('This product is already added in the list.');
                        return;
                    }

                    // Calculate row count based on existing rows
                    let row_count = $('#product-table-body tr.purchase_entry_row').length;

                    $.ajax({
                        method: 'POST',
                        // Fix URL to match route
                        url: "{{ route('purchase.get_purchase_entry_row') }}",
                        dataType: 'html',
                        data: {
                            product_id: product_id,
                            row_count: row_count,
                            variation_id: variation_id,
                            location_id: $('#location_id').val(), // Ensure location_id is passed
                            _token: "{{ csrf_token() }}" // Ensure CSRF token is passed
                        },
                        success: function(result) {
                            // Append result directly
                            $('#product-table-body').append(result);
                            
                            // Hide "No items" row
                            $('#no-items-row').hide();

                            // Trigger calculation update
                            // We can trigger a change event on the newly added quantity input
                            const newRow = $('#product-table-body tr:last');
                            newRow.find('.purchase_quantity').trigger('change');
                        },
                        error: function(xhr) {
                            console.error(xhr);
                            alert('Error adding product. Please try again.');
                        }
                    });
                }
            }

            // ===== ADD PRODUCT TO TABLE =====
            let productRowIndex = 1;

            function addProductToTable(id, name, cost) {
                const $tbody = $('#product-table-body');
                const $noRow = $('#no-items-row');

                // Remove "no items" row if present
                if ($noRow.length) $noRow.remove();

                // Create a basic row (you should enhance this with real fields: tax, discount, etc.)
                const row = `
                    <tr data-product-id="${id}">
                        <td>${productRowIndex}</td>
                        <td>${name}</td>
                        <td><input type="number" class="form-control purchase-qty" name="products[${id}][quantity]" value="1" min="1"></td>
                        <td>0</td>
                        <td><input type="number" class="form-control unit-cost" name="products[${id}][unit_cost]" value="${cost}" step="0.01"></td>
                        <td><input type="number" class="form-control discount-pct" name="products[${id}][discount_percent]" value="0" min="0" max="100"></td>
                        <td class="unit-cost-after-discount">${cost.toFixed(2)}</td>
                        <td class="subtotal">${cost.toFixed(2)}</td>
                        <td>0.00</td>
                        <td>${cost.toFixed(2)}</td>
                        <td class="line-total">${cost.toFixed(2)}</td>
                        <td><input type="number" class="form-control margin" name="products[${id}][margin_percent]" value="0"></td>
                        <td><input type="number" class="form-control selling-price" name="products[${id}][selling_price]" value="0" step="0.01"></td>
                        <td><button type="button" class="btn btn-danger btn-sm remove-product">✕</button></td>
                    </tr>
                `;

                $tbody.append(row);
                productRowIndex++;

                // Attach event to remove button
                $tbody.find('.remove-product').last().on('click', function() {
                    $(this).closest('tr').remove();
                    if ($('#product-table-body tr').length === 0) {
                        $('#product-table-body').append(`
                            <tr id="no-items-row">
                                <td colspan="14" class="text-center py-4 text-muted">
                                    <i class="fas fa-box-open fa-2x mb-2"></i><br>
                                    {{ __('No items added yet. Search and select products above to add them.') }}
                                </td>
                            </tr>
                        `);
                    }
                });

                // Optional: Attach change events to recalculate row totals (simplified)
                $tbody.find('tr').last().find('.purchase-qty, .unit-cost, .discount-pct').on('input', function() {
                    // You can add real-time calculation here
                });
            }
        });
    </script>
@endpush
