@extends('layouts.app')

@push('page-styles')
    <style>
        #product-select {
            width: 100%;
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
            padding: 0;
            font-size: 1rem;
            line-height: 1.5;
            background-color: #fff;
            background-clip: padding-box;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
            position: absolute;
            z-index: 1000;
            max-height: 200px;
            overflow-y: auto;
            display: none;
            /* Hidden by default */
            list-style: none;
            margin-top: 2px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        #product-select option {
            padding: 8px 12px;
            cursor: pointer;
        }

        #product-select option:hover {
            background-color: #f8f9fa;
            color: #007bff;
        }

        .form-group.product-search-container {
            position: relative;
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
                                        <option value="1">dd</option>
                                        @foreach ($suppliers ?? [] as $supplier)
                                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
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
                                    <option value="pending">Pending</option>
                                    <option value="received">Received</option>
                                    <option value="ordered">Ordered</option>
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
                                <x-form.select name="location_id">
                                    <option value="" disabled selected>-- Select Location --</option>
                                    <option value="1">29copy</option>
                                    @foreach ($businessLocation ?? [] as $location)
                                        <option value="{{ $location->id }}">{{ $location->name }}</option>
                                    @endforeach
                                </x-form.select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="input-block mb-3">
                                <x-form.label>{{ __('Store') }}</x-form.label>
                                <x-form.select name="store_id">
                                    <option value="" disabled selected>-- Select Store --</option>
                                    <option value="1">Main Store</option>
                                    @foreach ($stores ?? [] as $store)
                                        <option value="{{ $store->id }}">{{ $store->name }}</option>
                                    @endforeach
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
                            <select id="product-select" size="8">

                                @foreach ($products ?? [] as $product)
                                    <option value="{{ $product['id'] }}" data-name="{{ $product['name'] }}"
                                        data-code="{{ $product['id'] }}">
                                        {{ $product['name'] }} ({{ $product['id'] }})
                                    </option>
                                @endforeach
                            </select>
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
                                    <strong>{{ __('Discount:') }} <span id="display-grand-total">0.00</span></strong>
                                    <input type="hidden" id="discount_amount" value=0 name="discount_amount">
                                </h5>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6"></div>
                            <div class="col-md-6 text-end">
                                <h5 class="mb-0">
                                    <strong>{{ __('Net Total:') }} <span id="display-grand-total">0.00</span></strong>
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
                                <div>
                                    <strong>{{ __('Payment Due') }}:</strong>
                                    <span id="payment_due" class="text-danger fs-5">0.00</span>
                                </div>
                            </div>

                            <div id="payment_rows_div">
                                {{-- Initial payment row --}}
                                @include('purchase::partials.payment_row', ['row_index' => 0, 'payment_types' => $payment_types ?? [], 'accounts' => $accounts ?? []])
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
    @if(!isset($jquery_loaded))
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        @php $jquery_loaded = true; @endphp
    @endif
    
    {{-- Purchase creation JavaScript --}}
    <script src="{{ asset('modules/purchase/assets/js/purchase.js') }}"></script>
@endpush
