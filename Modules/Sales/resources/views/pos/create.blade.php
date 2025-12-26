@extends('layouts.blank')

@section('content')
<div class="pos-container">
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
            <button class="btn btn-outline-secondary btn-sm" id="full-screen-btn">
                <i class="fa fa-expand"></i>
            </button>
            <a href="{{ route('sales.index') }}" class="btn btn-danger btn-sm">
                <i class="fa fa-sign-out"></i> {{ __('Exit') }}
            </a>
        </div>
    </header>

    <input type="hidden" id="location_id" value="{{ $default_location_id }}">
    <input type="hidden" id="store_id" value="{{ $default_store_id }}">
    <div class="pos-body">
        <div class="row g-0 h-100">
            {{-- Left Column: Cart --}}
            <div class="col-md-7 pos-cart-section">
                <div class="cart-header p-3">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="fa fa-user"></i></span>
                                <select name="contact_id" id="contact_id" class="form-select select2">
                                    @foreach($customers as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="fa fa-search"></i></span>
                                <input type="text" id="pos_search_product" class="form-control" placeholder="{{ __('Search Product (Name/Code/Barcode)') }}">
                                <div id="pos_search_results" class="pos-search-dropdown shadow-sm" style="display: none;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="cart-table-container">
                    <table class="table pos-table" id="pos-table">
                        <thead>
                            <tr>
                                <th style="width: 40%">{{ __('Product') }}</th>
                                <th style="width: 25%" class="text-center">{{ __('Qty') }}</th>
                                <th style="width: 20%" class="text-end">{{ __('Subtotal') }}</th>
                                <th style="width: 15%" class="text-center"><i class="fa fa-times text-danger"></i></th>
                            </tr>
                        </thead>
                        <tbody id="pos-cart-body">
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
                            <span>{{ __('Total Before Tax') }}:</span>
                            <strong id="total-before-tax">0.00</strong>
                        </div>
                        <div class="summary-item">
                            <span>{{ __('Tax') }}:</span>
                            <strong id="total-tax">0.00</strong>
                        </div>
                        <div class="summary-item total-row">
                            <span>{{ __('Payable') }}:</span>
                            <strong id="payable-amount">0.00</strong>
                        </div>
                    </div>
                    <div class="cart-actions mt-3">
                        <div class="row g-2">
                            <div class="col">
                                <button type="button" class="btn btn-warning w-100 py-3 font-weight-bold" id="suspend-btn">
                                    <i class="fa fa-pause"></i> {{ __('Suspend') }}
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
            </div>
        </div>
    </div>
</div>

{{-- Payment Modal --}}
@include('sales::pos.partials.payment_modal')

<style>
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
    .pos-logo {
        font-size: 1.5rem;
        font-weight: 800;
        color: #1a237e;
        margin-right: 20px;
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
</style>

@push('page-script')
<script>
    $(document).ready(function() {
        // Initialize clock
        setInterval(() => {
            $('#pos-clock').text(moment().format('YYYY-MM-DD HH:mm:ss'));
        }, 1000);

        // Load Initial Products
        loadProducts();

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
                    location_id: $('#location_id').val()
                },
                success: function(products) {
                    let html = '';
                    products.forEach(p => {
                        html += `<div class="search-result-item" data-id="${p.variation_id}">
                                    <div class="d-flex justify-content-between">
                                        <strong>${p.name}</strong>
                                        <span class="text-danger">${p.selling_price}</span>
                                    </div>
                                    <small class="text-muted">${p.sku}</small>
                                 </div>`;
                    });
                    $('#pos_search_results').html(html).show();
                }
            });
        });

        $(document).on('click', '.search-result-item', function() {
            const variationId = $(this).data('id');
            $('#pos_search_product').val('');
            $('#pos_search_results').hide();
            addProductToCart(variationId);
        });

        // Browser Filters
        $('#category_filter, #brand_filter').on('change', function() {
            loadProducts();
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
                calculatePosTotals();
            });
        });

        $(document).on('change input', '.qty-input', function() {
            const row = $(this).closest('tr');
            const qty = parseFloat($(this).val()) || 0;
            const unitPrice = parseFloat(row.find('.unit_price_hidden').val()) || 0;
            const subtotal = qty * unitPrice;
            row.find('.pos-subtotal-text').text(subtotal.toFixed(2));
            calculatePosTotals();
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
            const payable = parseFloat($('#payable-amount').text()) || 0;
            $('#payment-total-payable').val(payable.toFixed(2));
            $('#payment-amount-0').val(payable.toFixed(2)).trigger('change');
            $('#paymentModal').modal('show');
        });

        // Form Submission Logic
        $('#complete-sale-btn').on('click', function() {
            const form = $('#pos-sale-form');
            const data = form.serialize() + '&' + $('#pos-cart-form').serialize();
            
            $(this).attr('disabled', true).text("{{ __('Processing...') }}");

            $.ajax({
                url: "{{ route('sales.pos.store') }}",
                method: 'POST',
                data: data,
                success: function(result) {
                    if (result.success) {
                        toastr.success(result.msg);
                        location.reload(); // Or show receipt print modal
                    } else {
                        toastr.error(result.msg);
                        $('#complete-sale-btn').attr('disabled', false).text("{{ __('COMPLETE SALE') }}");
                    }
                },
                error: function() {
                    toastr.error("{{ __('Something went wrong') }}");
                    $('#complete-sale-btn').attr('disabled', false).text("{{ __('COMPLETE SALE') }}");
                }
            });
        });

        function loadProducts() {
            // Mock product loading - in real app, fetch from server
            $.ajax({
                url: "{{ route('sales.pos.get_products') }}", 
                data: {
                    category_id: $('#category_filter').val(),
                    brand_id: $('#brand_filter').val(),
                    location_id: $('#location_id').val(),
                    store_id: $('#store_id').val()
                },
                success: function(products) {
                    let html = '';
                    products.forEach(p => {
                        html += `
                            <div class="product-card" data-id="${p.variation_id}">
                                <div class="product-card-img">
                                    <i class="fa fa-cube fa-2x text-muted"></i>
                                    <span class="card-stock-badge">${p.current_stock || 0} ${p.unit || ''}</span>
                                </div>
                                <div class="product-card-info">
                                    <div class="product-card-name">${p.name}</div>
                                    <div class="product-card-price">${p.selling_price}</div>
                                </div>
                            </div>
                        `;
                    });
                    $('#product-grid').html(html);
                }
            });
        }

        function addProductToCart(variationId) {
            // Check if already in cart
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
                success: function(html) {
                    $('#pos-cart-body').append(html);
                    $('#empty-cart-msg').hide();
                    calculatePosTotals();
                }
            });
        }

        function calculatePosTotals() {
            let total = 0;
            let items = 0;
            $('#pos-cart-body tr').each(function() {
                const qty = parseFloat($(this).find('.qty-input').val()) || 0;
                const price = parseFloat($(this).find('.unit_price_hidden').val()) || 0;
                total += qty * price;
                items += qty;
            });

            $('#total-items').text(items);
            $('#total-before-tax').text(total.toFixed(2));
            $('#payable-amount').text(total.toFixed(2)); // Simplified: no global tax/discount yet
            
            if (items === 0) {
                $('#empty-cart-msg').show();
            }
        }
    });
</script>
@endpush
@endsection
