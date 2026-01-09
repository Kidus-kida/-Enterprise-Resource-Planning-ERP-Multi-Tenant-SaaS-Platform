@extends('layouts.app')

@section('page-content')
<div class="pos-container no-print">
    {{-- POS Header --}}
    <header class="pos-header">
        <div class="header-left">
            <span class="pos-logo">POS</span>
            <div class="header-info">
                <span class="badge bg-primary location-badge">{{ $business_locations->first() }}</span>
                <span id="pos-clock" class="text-muted ms-2"></span>
            </div>
        </div>
        <div class="header-right">
            <button type="button" class="btn btn-sm btn-outline-primary" id="recent-transactions-btn">
                <i class="fa fa-history"></i> {{ __('Recent Transactions') }}
            </button>
            <button type="button" class="btn btn-sm btn-outline-danger ms-2" onclick="window.location.href='{{ route('dashboard') }}'">
                <i class="fa fa-power-off"></i>
            </button>
        </div>
    </header>

    <form id="pos-sale-form-main" action="{{ !empty($transaction) ? route('sales.pos.update', [$transaction->id]) : route('sales.pos.store') }}" method="POST">
        @if(!empty($transaction))
            @method('PUT')
        @endif
        @csrf
        <input type="hidden" name="location_id" id="location_id" value="{{ $default_location_id }}" data-default_accounts="{{ $default_location->default_payment_accounts ?? '' }}">
        <input type="hidden" name="store_id" id="store_id" value="{{ $default_store_id }}">
        {{-- contact_id is provided by the select element below --}}
        
        <input type="hidden" name="discount_type" id="discount_type" value="{{ $transaction->discount_type ?? 'fixed' }}">
        <input type="hidden" name="discount_amount" id="discount_amount" value="{{ $transaction->discount_amount ?? 0 }}">
        <input type="hidden" name="tax_rate_id" id="tax_rate_id" value="{{ $transaction->tax_id ?? '' }}">
        <input type="hidden" name="tax_amount" id="tax_amount" value="{{ $transaction->tax_amount ?? 0 }}">
        <input type="hidden" name="final_total" id="final_total" value="{{ $transaction->final_total ?? 0 }}">
        <input type="hidden" name="status" id="pos_status" value="{{ $transaction->status ?? 'final' }}">
        <input type="hidden" name="is_duplicate" value="0"> {{-- This was in the old form, keeping it --}}
        @if(!empty($transaction))
            <input type="hidden" name="transaction_id" id="transaction_id" value="{{ $transaction->id }}">
        @endif
        
        <div class="pos-body">
            <div class="row g-0 h-100">
                {{-- Left Column: Cart --}}
                <div class="col-md-7 pos-cart-section">
                    <div class="cart-header p-3">
                        <div class="row g-2">
                            <div class="col-md-6">
                                    <div class="input-group">
                                        <div style="display: flex; align-items: center; gap: 5px; width: 100%;">
                                            <span class="input-group-text bg-white"><i class="fa fa-user"></i></span>
                                            <select name="contact_id" id="contact_id" class="form-select select2">
                                                    <option value="" 
                                                        @if(empty($transaction) || (!empty($transaction) && empty($transaction->contact_id))) selected @endif>
                                                        {{ __('Walk-in Customer') }}
                                                    </option>
                                                @foreach($customers as $id => $name)
                                                    <option value="{{ $id }}" @if(!empty($transaction) && $transaction->contact_id == $id) selected @endif>{{ $name }}</option>
                                                @endforeach
                                            </select>
                                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#quick_add_contact_modal">
                                                <i class="fa fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                <div class="customer-info-div mt-2" style="display: none; font-size: 0.85rem;">
                                    <span class="text-muted">{{ __('Customer') }}:</span> <span class="customer_name fw-bold text-dark"></span>
                                    <span class="text-muted ms-3">{{ __('Due') }}:</span> <span class="customer_due_amount fw-bold text-danger">0.00</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="position-relative">
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="fa fa-search"></i></span>
                                        <input type="text" id="pos_search_product" class="form-control" placeholder="{{ __('Search Product (Name/Code/Barcode)') }}" autocomplete="off">
                                    </div>
                                    <div id="pos_search_results" class="pos-search-dropdown shadow-sm" style="display: none;"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="cart-table-container">
                        <table class="table pos-table" id="pos-table">
                            <thead>
                                <tr>
                                    <th style="width: 35%">{{ __('Product') }}</th>
                                    <th style="width: 20%" class="text-center">{{ __('Qty') }}</th>
                                    <th style="width: 15%" class="text-end">{{ __('Price') }}</th>
                                    <th style="width: 20%" class="text-end">{{ __('Subtotal') }}</th>
                                </tr>
                            </thead>
                            <tbody id="pos-cart-body">
                                @if(!empty($transaction))
                                    @foreach($transaction->sell_lines as $sell_line)
                                        @include('sales::pos.partials.product_row', ['sell_line' => $sell_line])
                                    @endforeach
                                @endif
                                {{-- Rows added dynamically --}}
                            </tbody>
                        </table>
                        <div id="empty-cart-msg" class="text-center p-5 text-muted">
                            <i class="fa fa-shopping-cart fa-3x mb-3"></i>
                            <p>{{ __('Cart is empty') }}</p>
                        </div>
                    </div>

                    <div class="cart-footer">
                        <div class="summary-grid">
                            <div class="summary-item">
                                <span>{{ __('Items') }}:</span>
                                <strong id="total-items">0</strong>
                            </div>
                            <div class="summary-item">
                                <span>{{ __('Subtotal') }}:</span>
                                <strong id="total-before-tax">0.00</strong>
                            </div>
                            <div class="summary-item">
                                <span>{{ __('Discount') }} (-): <i class="fa fa-pencil-alt la la-edit cursor-pointer text-primary ms-1" data-bs-toggle="modal" data-bs-target="#posEditDiscountModal" title="{{ __('Edit Discount') }}"></i></span>
                                <strong id="total-discount">0.00</strong>
                            </div>
                            <div class="summary-item">
                                <span>{{ __('Order Tax') }} (+): <i class="fa fa-pencil-alt la la-edit cursor-pointer text-primary ms-1" data-bs-toggle="modal" data-bs-target="#posEditOrderTaxModal" title="{{ __('Edit Order Tax') }}"></i></span>
                                <strong id="total-tax">0.00</strong>
                            </div>
                            <div class="summary-item total-row">
                                <span>{{ __('Payable') }}:</span>
                                <strong id="payable-amount">0.00</strong>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-2 mb-2 px-1">
                            <div class="form-check form-switch cursor-pointer">
                                <input class="form-check-input" type="checkbox" name="is_recurring" value="1" id="is_recurring">
                                <label class="form-check-label text-dark" for="is_recurring" style="font-weight: 500;">{{ __('Recurring / Subscribe') }}</label>
                            </div>
                            <button type="button" class="btn btn-sm btn-link text-decoration-none" data-bs-toggle="modal" data-bs-target="#recurringInvoiceModal">
                                <i class="fa fa-cog"></i> {{ __('Config') }}
                            </button>
                        </div>
                        <div class="cart-actions mt-3">
                            <div class="row g-2">
                                <div class="col">
                                    <button type="button" class="btn btn-warning w-100 py-3 font-weight-bold" id="quotation-btn">
                                        <i class="fa fa-file-text-o"></i> {{ __('Quotation') }}
                                    </button>
                                </div>
                                <div class="col">
                                    <button type="button" class="btn btn-info w-100 py-3 text-white font-weight-bold" id="draft-btn">
                                        <i class="fa fa-file-text"></i> {{ __('Draft') }}
                                    </button>
                                </div>
                                <div class="col-6">
                                    <button type="button" class="btn btn-success w-100 py-3 font-weight-bold btn-pay" id="pay-btn">
                                        <i class="fa fa-money"></i> {{ __('PAY & COMPLETE') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right Column: Product Browser --}}
                <div class="col-md-5 pos-browser-section">
                    <div class="browser-header p-3 border-bottom">
                        <div class="row g-2">
                            <div class="col">
                                <select id="category_filter" class="form-select select2">
                                    <option value="">{{ __('All Categories') }}</option>
                                    @foreach($categories as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col">
                                <select id="brand_filter" class="form-select select2">
                                    <option value="">{{ __('All Brands') }}</option>
                                    @foreach($brands as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="product-grid" id="product-grid">
                        {{-- Product cards added dynamically --}}
                    </div>
                    <div id="product-pagination" class="p-3 border-top text-center" style="display: none;">
                        <button type="button" class="btn btn-primary w-100 py-2" id="load-more-btn">
                            <i class="fa fa-refresh me-1"></i> {{ __('Load More') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @include('sales::partials.recurring_invoice_modal')
    </form>
</div>

{{-- Payment Modal --}}
@include('sales::pos.partials.payment_modal')

<!-- Quick Add Contact Modal -->
<div class="modal fade" id="quick_add_contact_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="quick_add_contact_form">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">{{ __('Quick Add Customer') }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('Name') }}*</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Mobile') }}</label>
                        <input type="text" name="mobile" class="form-control">
                    </div>
                    <input type="hidden" name="type" value="customer">
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary w-100" id="quick_add_contact_submit">{{ __('Save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Discount Modal -->
<div class="modal fade" id="posEditDiscountModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">{{ __('Edit Discount') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{{ __('Discount Type') }}</label>
                        <select id="discount_type_modal" class="form-select">
                            <option value="fixed">{{ __('Fixed') }}</option>
                            <option value="percentage">{{ __('Percentage') }}</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{{ __('Discount Amount') }}</label>
                        <input type="number" id="discount_amount_modal" class="form-control" value="0" step="0.01">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary w-100" id="posEditDiscountModalUpdate">{{ __('Update') }}</button>
            </div>
        </div>
    </div>
</div>

<!-- Tax Modal -->
<div class="modal fade" id="posEditOrderTaxModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">{{ __('Edit Order Tax') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">{{ __('Order Tax') }}</label>
                    <select id="order_tax_modal" class="form-select">
                        <option value="">{{ __('No Tax') }}</option>
                        @foreach($taxes as $tax)
                            <option value="{{ $tax->id }}" data-amount="{{ $tax->amount }}">{{ $tax->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary w-100" id="posEditOrderTaxModalUpdate">{{ __('Update') }}</button>
            </div>
        </div>
    </div>
</div>

{{-- Recent Transactions Modal --}}
<div class="modal fade" id="recent_transactions_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Recent Transactions') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="recent_transactions_body">
                {{-- Loaded via AJAX --}}
            </div>
        </div>
    </div>
</div>

{{-- Receipt Print Frame --}}
{{-- Receipt Print Section --}}
<div id="receipt_section" class="print_section"></div>

<style>
    @media print {
        .no-print { display: none !important; }
        .print_section { display: block !important; width: 100%; height: 100%; position: absolute; top: 0; left: 0; background: white; z-index: 9999; }
        @page { size: auto; margin: 0mm; }
        body { background-color: #fff; margin:0; padding: 0; }
        
        /* Compatibility for legacy Bootstrap 3 classes used in receipt templates */
        .col-xs-12 { width: 100%; flex: 0 0 auto; float: left; }
        .col-xs-6 { width: 50%; flex: 0 0 auto; float: left; }
        .pull-left { float: left !important; }
        .pull-right { float: right !important; }
        .text-center { text-align: center !important; }
        .text-left { text-align: left !important; }
        .text-right { text-align: right !important; }
        .img-responsive { max-width: 100%; height: auto; }
        .centered { text-align: center; }
    }
    .print_section { display: none; }

    /* CSS Styles for POS */
    .pos-container {
        height: 100vh;
        display: flex;
        flex-direction: column;
        background: #f4f7f6;
        overflow: hidden;
    }
    .pos-header {
        height: 50px;
        background: #fff;
        border-bottom: 1px solid #ddd;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 15px;
        z-index: 100;
    }
    .header-left {
        display: flex;
        align-items: center;
        flex-wrap: nowrap;
    }
    .header-info {
        display: flex;
        align-items: center;
        white-space: nowrap;
    }
    .header-right {
        display: flex;
        align-items: center;
        flex-wrap: nowrap;
    }
    .pos-logo {
        font-size: 1.5rem;
        font-weight: 800;
        color: #1a237e;
        margin-right: 20px;
        white-space: nowrap;
    }
    .pos-body {
        flex: 1;
        overflow: hidden;
    }
    .pos-cart-section {
        background: #fff;
        border-right: 1px solid #ddd;
        display: flex;
        flex-direction: column;
    }
    .cart-table-container {
        flex: 1;
        overflow-y: auto;
    }
    .pos-table th {
        background: #f8f9fa;
        font-size: 0.85rem;
        text-transform: uppercase;
        color: #666;
        padding: 12px 15px;
    }
    .pos-table td {
        vertical-align: middle;
        padding: 10px 15px;
        border-bottom: 1px solid #f1f1f1;
    }
    .cart-footer {
        background: #fff;
        border-top: 1px solid #ddd;
        padding: 15px;
    }
    .summary-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
    }
    .summary-grid .summary-item {
        display: flex;
        flex-direction: column;
    }
    .summary-grid .summary-item span {
        font-size: 0.75rem;
        color: #777;
    }
    .summary-grid .summary-item strong {
        font-size: 1.1rem;
    }
    .total-row {
        grid-column: span 3;
        border-top: 1px dashed #ccc;
        padding-top: 10px;
        margin-top: 5px;
        flex-direction: row !important;
        justify-content: space-between;
        align-items: center;
    }
    .total-row strong {
        font-size: 1.8rem !important;
        color: #2e7d32;
    }
    .pos-browser-section {
        background: #ebedef;
        display: flex;
        flex-direction: column;
    }
    .product-grid {
        flex: 1;
        overflow-y: auto;
        padding: 15px;
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        gap: 12px;
    }
    .product-card {
        background: #fff;
        border-radius: 8px;
        border: 1px solid #ddd;
        overflow: hidden;
        cursor: pointer;
        transition: transform 0.2s, box-shadow 0.2s;
        display: flex;
        flex-direction: column;
    }
    .product-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .product-card-img {
        height: 100px;
        background: #eee;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
    }
    .card-stock-badge {
        position: absolute;
        top: 5px;
        right: 5px;
        background: rgba(0,0,0,0.6);
        color: #fff;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 0.7rem;
    }
    .product-card-info {
        padding: 10px;
        text-align: center;
        flex: 1;
    }
    .product-card-name {
        font-size: 0.85rem;
        font-weight: 600;
        margin-bottom: 5px;
        height: 2.4em;
        overflow: hidden;
        line-height: 1.2;
    }
    .product-card-price {
        color: #d32f2f;
        font-weight: 800;
        font-size: 1rem;
    }
    .pos-search-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: #fff;
        z-index: 1000;
        max-height: 300px;
        overflow-y: auto;
        border: 1px solid #ddd;
        border-top: none;
        border-radius: 0 0 8px 8px;
    }
    .search-result-item {
        padding: 10px 15px;
        border-bottom: 1px solid #eee;
        cursor: pointer;
    }
    .search-result-item:hover {
        background: #f8f9fa;
    }
    .qty-input-group {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .qty-input {
        width: 50px;
        text-align: center;
        border: 1px solid #ddd;
        margin: 0 5px;
    }
    .btn-qty {
        width: 28px;
        height: 28px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
    }
    .unit_price_hidden {
        max-width: 100px;
        display: inline-block;
        border: 1px solid #eee;
        background: transparent;
    }
    .unit_price_hidden:focus {
        background: #fff;
    }
    .cursor-pointer {
        cursor: pointer;
    }
</style>

@push('page-scripts')
<script type="module">
    // --- Global POS Functions (available via window) ---
    window.pos_print = pos_print;
    window.calculatePaymentTotals = calculatePaymentTotals;
    window.submitPosForm = submitPosForm;
    window.loadProducts = loadProducts;
    window.addProductToCart = addProductToCart;
    window.calculateTotals = calculateTotals;

    // --- POS Function Definitions (Hoisted in Module Scope) ---

    function pos_print(receipt) {
        console.log('Printing receipt:', receipt);
        if (!receipt || !receipt.html_content) {
            console.error('No receipt content to print');
            return;
        }
        if (receipt.print_type == 'browser') {
            var iframe = $('<iframe id="receipt_iframe" style="display:none"></iframe>').appendTo('body')[0];
            var doc = iframe.contentDocument || iframe.contentWindow.document;
            doc.open();
            doc.write(receipt.html_content);
            doc.close();
            
            setTimeout(function() {
                iframe.contentWindow.focus();
                iframe.contentWindow.print();
                setTimeout(function() { $(iframe).remove(); }, 1000);
            }, 500);
        } else if (receipt.print_type == 'printer') {
            toastr.info("Printing to " + (receipt.printer_config.name || 'Printer'));
        }
    }

    function calculatePaymentTotals() {
        let totalPayable = parseFloat($('#payment-total-payable').val()) || 0;
        let totalPaid = 0;
        $('.payment_amount').each(function() {
            totalPaid += parseFloat($(this).val()) || 0;
        });

        $('#payment-total-paid').text(totalPaid.toFixed(2));
        let balance = totalPayable - totalPaid;
        if ($('#payment-balance-due').length) {
            $('#payment-balance-due').text(balance.toFixed(2));
        }
    }

    function submitPosForm() {
        calculateTotals(); // Ensure all hidden fields (discount, tax) are updated
        
        const formData = $('#pos-sale-form-main').serializeArray();
        const paymentData = $('#pos-sale-form').serializeArray();
        
        // Merge form data
        const combinedData = formData.concat(paymentData);
        console.log('Submitting POS Form with data:', combinedData);

        const form = $('#pos-sale-form-main');
        const url = form.attr('action');
        const method = form.attr('method');

        const btn = $('#complete-sale-btn');
        const originalText = btn.text();
        
        btn.attr('disabled', true).text("{{ __('Processing...') }}");

        $.ajax({
            url: url,
            method: method,
            data: combinedData,
            success: function(result) {
                btn.prop('disabled', false).text(originalText);
                if (result.success) {
                    toastr.success(result.msg);
                    
                    if (result.receipt && result.receipt.html_content) {
                        pos_print(result.receipt);
                    }
                    
                    // If it was an update, maybe redirect back to create or just refresh?
                    if (combinedData.find(d => d.name === '_method' && d.value === 'PUT')) {
                        window.location.href = "{{ route('sales.pos.create') }}";
                        return;
                    }

                    // Reset cart
                    $('#pos-cart-body').empty();
                    $('#contact_id').val('').trigger('change');
                    $('#discount_type').val('fixed');
                    $('#discount_amount').val('0');
                    $('#tax_rate_id').val('');
                    $('#order_tax_modal').val('');
                    calculateTotals();
                    $('#paymentModal').modal('hide');
                    loadProducts();
                } else {
                    btn.attr('disabled', false).text(originalText);
                    toastr.error(result.msg);
                }
            },
            error: function(xhr) {
                btn.attr('disabled', false).text(originalText);
                toastr.error("{{ __('Something went wrong') }}");
            }
        });
    }

    let product_page = 1;
    let last_page = 1;

    function loadProducts(append = false) {
        console.log('Loading products page:', product_page);
        const btn = $('#load-more-btn');
        const originalText = btn.html();
        
        if (append) {
            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Loading...');
        }

        $.ajax({
            url: "{{ route('sales.pos.get_products') }}", 
            data: {
                category_id: $('#category_filter').val(),
                brand_id: $('#brand_filter').val(),
                location_id: $('#location_id').val(),
                store_id: $('#store_id').val(),
                page: product_page,
                per_page: 24
            },
            success: function(response) {
                let products = response.data || response;
                last_page = response.last_page || 1;

                let html = '';
                if (products.length > 0) {
                    products.forEach(p => {
                        html += `
                            <div class="product-card" data-id="${p.variation_id}">
                                <div class="product-card-img">
                                    <i class="fa fa-cube fa-2x text-muted"></i>
                                    <span class="card-stock-badge">${p.current_stock || 0} ${p.unit || ''}</span>
                                </div>
                                <div class="product-card-info">
                                    <div class="product-card-name">${p.name}</div>
                                    <div class="product-card-price">${p.default_sell_price}</div>
                                </div>
                            </div>
                        `;
                    });
                    
                    if (append) {
                        $('#product-grid').append(html);
                    } else {
                        $('#product-grid').html(html);
                    }

                    if (product_page < last_page) {
                        $('#product-pagination').show();
                    } else {
                        $('#product-pagination').hide();
                    }
                } else if (!append) {
                    $('#product-grid').html('<div class="col-12 text-center p-5 text-muted">No products available.</div>');
                    $('#product-pagination').hide();
                }

                if (append) btn.prop('disabled', false).html(originalText);
            },
            error: function(xhr) {
                if (append) btn.prop('disabled', false).html(originalText);
            }
        });
    }

    function addProductToCart(variationId) {
        let existingRow = $(`#pos-cart-body tr[data-variation-id="${variationId}"]`);
        if (existingRow.length > 0) {
            const qtyInput = existingRow.find('.qty-input');
            qtyInput.val(parseFloat(qtyInput.val()) + 1).trigger('change');
            return;
        }

        $.ajax({
            url: "{{ route('sales.pos.get_product_row') }}",
            data: { 
                variation_id: variationId,
                location_id: $('#location_id').val(),
                store_id: $('#store_id').val()
            },
            success: function(response) {
                if (response.success === false) {
                    toastr.error(response.msg);
                    return;
                }
                $('#pos-cart-body').append(response);
                $('#empty-cart-msg').hide();
                calculateTotals();
            }
        });
    }

    function calculateTotals() {
        let total = 0;
        let items = 0;
        $('#pos-cart-body tr').each(function() {
            let qty = parseFloat($(this).find('.qty-input').val()) || 0;
            let price = parseFloat($(this).find('.unit_price_hidden').val()) || 0;
            let subtotal = qty * price;
            $(this).find('.pos-subtotal-text').text(subtotal.toFixed(2));
            total += subtotal;
            items += qty;
        });

        let discountType = $('#discount_type').val();
        let discountAmount = parseFloat($('#discount_amount').val()) || 0;
        let totalDiscount = discountType === 'fixed' ? discountAmount : (total * discountAmount) / 100;

        let totalAfterDiscount = Math.max(0, total - totalDiscount);
        let taxRate = 0;
        let taxId = $('#tax_rate_id').val();
        if (taxId) {
            taxRate = parseFloat($('#order_tax_modal option[value="' + taxId + '"]').data('amount')) || 0;
        }
        let totalTax = (totalAfterDiscount * taxRate) / 100;
        let payable = totalAfterDiscount + totalTax;

        $('#total-items').text(items);
        $('#total-before-tax').text(total.toFixed(2));
        $('#total-discount').text(totalDiscount.toFixed(2));
        $('#total-tax').text(totalTax.toFixed(2));
        $('#payable-amount').text(payable.toFixed(2));

        $('#tax_amount').val(totalTax.toFixed(2));
        $('#final_total').val(payable.toFixed(2));
        $('#payment-total-payable').val(payable.toFixed(2));

        if (items === 0) $('#empty-cart-msg').show();
        else $('#empty-cart-msg').hide();
    }

    function loadPaymentAccounts(row, paymentMethod) {
        var accountDropdown = row.find('select[name$="[account_id]"]');
        if (!paymentMethod) {
            accountDropdown.html('<option value="">{{ __("Select Account") }}</option>');
            return;
        }

        $.ajax({
            url: "{{ route('sales.pos.get_payment_accounts') }}",
            method: 'GET',
            data: { payment_method: paymentMethod },
            dataType: 'json',
            success: function (accounts) {
                var current_val = accountDropdown.val();
                accountDropdown.html('<option value="">{{ __("Select Account") }}</option>');
                $.each(accounts, function (id, name) {
                    accountDropdown.append('<option value="' + id + '">' + name + '</option>');
                });
                
                var location_accounts = $('#location_id').data('default_accounts');
                if (location_accounts && location_accounts[paymentMethod]) {
                    accountDropdown.val(location_accounts[paymentMethod]).trigger('change');
                } else if (current_val) {
                    accountDropdown.val(current_val).trigger('change');
                }
            },
            error: function (xhr) {
                accountDropdown.html('<option value="">Error loading accounts</option>');
            }
        });
    }

    $(document).ready(function() {
        console.log('POS Script Loaded');
        
        // Initialize select2
        if ($('.select2').length > 0) {
            $('.select2').each(function() {
                var $this = $(this);
                $this.select2({
                    width: '100%',
                    dropdownParent: $this.parent()
                });
            });
        }

        // Payment amount change listener
        $(document).on('change input', '.payment_amount', function() {
            calculatePaymentTotals();
        });

        // Add payment row
        $('#add_payment_row_btn').on('click', function() {
            let row_index = $('.payment-row').length;
            $.ajax({
                url: "{{ route('sales.pos.get_payment_row') }}",
                data: { row_index: row_index },
                dataType: 'html',
                success: function(result) {
                    $('#payment_rows_container').append(result);
                    // Initialize select2 for new row
                    $('#payment_rows_container .select2').each(function() {
                        if (!$(this).data('select2')) {
                            $(this).select2({
                                width: '100%',
                                dropdownParent: $('#paymentModal')
                            });
                        }
                    });
                    calculatePaymentTotals();
                }
            });
        });

        // Remove payment row
        $(document).on('click', '.remove_payment_row', function() {
            $(this).closest('.payment-row').remove();
            calculatePaymentTotals();
        });

        // Initialize clock
        setInterval(() => {
            if ($('#pos-clock').length) {
                $('#pos-clock').text(moment().format('YYYY-MM-DD HH:mm:ss'));
            }
        }, 1000);

        // Initialization calls moved to bottom after definitions

        // Search Product Logic
        $('#pos_search_product').on('input', function() {
            const term = $(this).val();
            if (term.length < 2) {
                $('#pos_search_results').hide();
                return;
            }

            $.ajax({
                url: "{{ route('sales.pos.get_product_suggestion') }}",
                data: { 
                    term: term,
                    location_id: $('#location_id').val(),
                    store_id: $('#store_id').val()
                },
                success: function(products) {
                    console.log('Search Result:', products);
                    let html = '';
                    if (products.length > 0) {
                        products.forEach(p => {
                            html += `<div class="search-result-item" data-id="${p.variation_id}">
                                        <div class="d-flex justify-content-between">
                                            <strong>${p.name}</strong>
                                            <span class="text-danger">${p.default_sell_price}</span>
                                        </div>
                                        <small class="text-muted">${p.sku}</small>
                                    </div>`;
                        });
                        $('#pos_search_results').html(html).show();
                    } else {
                        $('#pos_search_results').html('<div class="p-3 text-muted">No products found</div>').show();
                    }
                },
                error: function(xhr) {
                    console.error('Search Ajax Error:', xhr);
                }
            });
        });

        $(document).on('click', '.search-result-item', function() {
            const variationId = $(this).data('id');
            $('#pos_search_product').val('');
            $('#pos_search_results').hide();
            addProductToCart(variationId);
        });

        // Click outside to hide search results
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.position-relative').length) {
                $('#pos_search_results').hide();
            }
        });

        // Customer Change Logic
        $('#contact_id').on('change', function() {
            const contactId = $(this).val();
            // $('#customer_id').val(contactId); // No longer needed as the select has name="contact_id"
            if (!contactId) {
                $('.customer-info-div').hide();
                return;
            }

            $.ajax({
                url: "{{ route('sales.pos.get_customer_due_details') }}",
                method: 'POST',
                data: { 
                    contact_id: contactId,
                    _token: "{{ csrf_token() }}"
                },
                success: function(result) {
                    const data = JSON.parse(result);
                    console.log('Customer Details:', data);
                    $('.customer_name').text(data.name);
                    $('.customer_due_amount').text(data.due);
                    $('.customer-info-div').fadeIn();
                },
                error: function(xhr) {
                    console.error('Customer Details Ajax Error:', xhr);
                }
            });
        }).trigger('change'); // Trigger on load for edit mode

        // Quick Add Contact Submit
        $('#quick_add_contact_form').on('submit', function(e) {
            e.preventDefault();
            const data = $(this).serialize();
            $.ajax({
                url: "{{ route('sales.pos.quick_add_contact') }}",
                method: 'POST',
                data: data,
                success: function(result) {
                    if (result.success) {
                        toastr.success(result.msg);
                        $('#quick_add_contact_modal').modal('hide');
                        // Add new option to dropdown and select it
                        const newOption = new Option(result.contact_name, result.contact_id, true, true);
                        $('#contact_id').append(newOption).trigger('change');
                        $('#quick_add_contact_form')[0].reset();
                    } else {
                        toastr.error(result.msg);
                    }
                }
            });
        });

        // Browser Filters
        $('#category_filter, #brand_filter').on('change', function() {
            product_page = 1;
            loadProducts(false);
        });

        $('#load-more-btn').on('click', function() {
            if (product_page < last_page) {
                product_page++;
                loadProducts(true);
            }
        });

        // Add Product via Grid
        $(document).on('click', '.product-card', function() {
            const variationId = $(this).data('id');
            addProductToCart(variationId);
        });

        // Cart Actions (Increase/Decrease/Remove)
        $(document).on('click', '.btn-qty-plus', function() {
            const input = $(this).siblings('.qty-input');
            input.val(parseFloat(input.val()) + 1).trigger('change');
        });

        $(document).on('click', '.btn-qty-minus', function() {
            const input = $(this).siblings('.qty-input');
            if (parseFloat(input.val()) > 1) {
                input.val(parseFloat(input.val()) - 1).trigger('change');
            }
        });

        $(document).on('click', '.remove-pos-row', function() {
            $(this).closest('tr').fadeOut(300, function() {
                $(this).remove();
                calculateTotals();
            });
        });

        $(document).on('change input', '.qty-input, .unit_price_hidden', function() {
            const row = $(this).closest('tr');
            
            // Quantity Check
            if ($(this).hasClass('qty-input')) {
                const maxQty = parseFloat(row.find('.max_qty_available').val()) || 0;
                let qty = parseFloat($(this).val());
                if (maxQty > 0 && qty > maxQty) {
                    toastr.error("{{ __('Only') }} " + maxQty + " {{ __('quantity available') }}");
                    $(this).val(maxQty);
                }
            }

            const qty = parseFloat(row.find('.qty-input').val()) || 0;
            const unitPrice = parseFloat(row.find('.unit_price_hidden').val()) || 0;
            const subtotal = qty * unitPrice;
            row.find('.pos-subtotal-text').text(subtotal.toFixed(2));
            calculateTotals();
        });

        // Sub-unit Change (Update Multiplier and Price)
        $(document).on('change', '.sub_unit', function() {
            const row = $(this).closest('tr');
            const multiplier = parseFloat($(this).find(':selected').data('multiplier')) || 1;
            row.find('.base_unit_multiplier').val(multiplier);
            
            // Update prices based on multiplier
            const basePriceIncTax = parseFloat(row.find('.unit_price_hidden').data('default-price')) || 0;
            const newPriceIncTax = basePriceIncTax * multiplier;
            
            row.find('.unit_price_hidden').val(newPriceIncTax.toFixed(2));
            row.find('.row-unit-price').text(newPriceIncTax.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            
            // Also update exc tax price if needed
            const basePriceExcTax = parseFloat(row.find('.unit_price_exc_tax').data('default-price')) || 0;
            const newPriceExcTax = basePriceExcTax * multiplier;
            row.find('.unit_price_exc_tax').val(newPriceExcTax.toFixed(2));

            // Trigger calc
            row.find('.qty-input').trigger('change');
        });

        // Full Screen Toggle
        $('#full-screen-btn').on('click', function() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen();
                $(this).find('i').removeClass('fa-expand').addClass('fa-compress');
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                    $(this).find('i').removeClass('fa-compress').addClass('fa-expand');
                }
            }
        });

        // Pay Button
        $('#pay-btn').on('click', function() {
            if ($('#pos-cart-body tr').length === 0) {
                toastr.warning("{{ __('Cart is empty') }}");
                return;
            }
            $('#pos_status').val('final');
            calculateTotals(); // Ensure totals are up-to-date before opening payment modal
            const payable = parseFloat($('#final_total').val()) || 0;
            $('#payment-total-payable').val(payable.toFixed(2));
            
            // If only one payment row and it's 0, set it to payable
            if ($('.payment-row').length === 1 && (parseFloat($('.payment_amount').val()) || 0) === 0) {
                $('.payment_amount').val(payable.toFixed(2)).trigger('change');
            }
            
            calculatePaymentTotals();
            $('#paymentModal').modal('show');
        });

        // Draft Button
        $('#draft-btn').on('click', function() {
            if ($('#pos-cart-body tr').length === 0) {
                toastr.warning("{{ __('Cart is empty') }}");
                return;
            }
            $('#pos_status').val('draft');
            submitPosForm();
        });

        // Quotation Button
        $('#quotation-btn').on('click', function() {
            if ($('#pos-cart-body tr').length === 0) {
                toastr.warning("{{ __('Cart is empty') }}");
                return;
            }
            $('#pos_status').val('quotation'); 
            submitPosForm();
        });

        // Form Submission Logic
        $('#complete-sale-btn').on('click', function() {
            submitPosForm();
        });

        // Recent Transactions Button
        $('#recent-transactions-btn').on('click', function() {
            $.ajax({
                url: "{{ route('sales.pos.get_recent_transactions') }}",
                success: function(html) {
                    $('#recent_transactions_body').html(html);
                    $('#recent_transactions_modal').modal('show');
                }
            });
        });

        $(document).on('click', '.print-invoice-link', function(e) {
            e.preventDefault();
            const url = $(this).attr('href');
            $.ajax({
                url: url,
                success: function(response) {
                    if (response.success && response.receipt) {
                        pos_print(response.receipt);
                    }
                }
            });
        });

        $(document).on('click', '.delete-sale', function(e) {
            e.preventDefault();
            const url = $(this).attr('href');
            if (confirm("{{ __('Are you sure you want to delete this sale?') }}")) {
                $.ajax({
                    url: url,
                    method: 'DELETE',
                    data: { _token: "{{ csrf_token() }}" },
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.msg);
                            $('#recent-transactions-btn').trigger('click');
                        } else {
                            toastr.error(response.msg);
                        }
                    }
                });
            }
        });

        // Modal Updates
        $('#posEditDiscountModalUpdate').on('click', function() {
            $('#discount_type').val($('#discount_type_modal').val());
            $('#discount_amount').val($('#discount_amount_modal').val());
            $('#posEditDiscountModal').modal('hide');
            calculateTotals();
        });

        $('#posEditOrderTaxModalUpdate').on('click', function() {
            $('#tax_rate_id').val($('#order_tax_modal').val());
            $('#posEditOrderTaxModal').modal('hide');
            calculateTotals();
        });

        // Initialize discount/tax modals with current values when opened
        $('#posEditDiscountModal').on('show.bs.modal', function () {
            $('#discount_type_modal').val($('#discount_type').val());
            $('#discount_amount_modal').val($('#discount_amount').val());
        });

        $('#posEditOrderTaxModal').on('show.bs.modal', function () {
            $('#order_tax_modal').val($('#tax_rate_id').val());
        });

        // Handle payment method change
        $(document).on('change', '.payment_method', function() {
            var payment_method = $(this).val();
            var row = $(this).closest('.payment-row');
            if (row.length === 0) row = $(this).closest('.row'); // Fallback for simple layouts
            
            loadPaymentAccounts(row, payment_method);
        });
        
        // Trigger initial check when payment modal opens
        $('#paymentModal').on('shown.bs.modal', function() {
            $('.payment_method').trigger('change');
        });

        // Initialization Calls (at the end to ensure functions are defined)
        loadProducts();
        calculateTotals();

    });
</script>
@endpush
@endsection
