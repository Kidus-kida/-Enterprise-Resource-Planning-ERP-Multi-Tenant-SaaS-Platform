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
            <x-slot name="title">{{ __('Edit Purchase') }}</x-slot>
            <ul class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
                </li>
                <li class="breadcrumb-item">
                     <a href="{{ route('purchase.index') }}">{{ __('Purchase') }}</a>
                </li>
                <li class="breadcrumb-item active">
                    {{ __('Edit') }}
                </li>
            </ul>
        </x-breadcrumb>

        <form action="{{ route('purchase.update', $transaction->id) }}" method="post" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="row">
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="input-block mb-3">
                                <x-form.label>{{ __('Purchase No') }}</x-form.label>
                                <x-form.input type="text" name="ref_no" value="{{ $transaction->ref_no }}" readonly />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="input-block mb-3">
                                <x-form.label>{{ __('Supplier') }}</x-form.label>
                                <x-form.select id="supplierSelect" name="contact_id" class="form-control">
                                    <option value="" disabled>-- Select Supplier --</option>
                                    @foreach ($suppliers as $key => $supplier)
                                        <option value="{{ $key }}" @if($transaction->contact_id == $key) selected @endif>{{ $supplier }}</option>
                                    @endforeach
                                </x-form.select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="input-block mb-3">
                                <x-form.label>{{ __('P. Invoice No') }}</x-form.label>
                                <x-form.input type="text" name="ref_no" value="{{ $transaction->ref_no }}" />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="input-block mb-3">
                                <x-form.label>{{ __('Received Date') }}</x-form.label>
                                <x-form.input type="date" name="transaction_date" value="{{ \Carbon\Carbon::parse($transaction->transaction_date)->format('Y-m-d') }}" />
                            </div>
                        </div>
                        <div class="col-md-4">
                             <div class="input-block mb-3">
                                <x-form.label>{{ __('Invoice Date') }}</x-form.label>
                                <x-form.input type="date" name="invoice_date" value="{{ !empty($transaction->invoice_date) ? \Carbon\Carbon::parse($transaction->invoice_date)->format('Y-m-d') : '' }}" />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="input-block mb-3">
                                <x-form.label>{{ __('Purchase Status') }}</x-form.label>
                                <x-form.select name="status">
                                    <option value="" disabled>-- Select Status --</option>
                                    @foreach($orderStatuses as $key => $status)
                                        <option value="{{ $key }}" @if($transaction->status == $key) selected @endif>{{ $status }}</option>
                                    @endforeach
                                </x-form.select>
                            </div>
                        </div>
                         <div class="col-md-4">
                            <div class="input-block mb-3">
                                <x-form.label>{{ __('VAT Invoice') }}</x-form.label>
                                <x-form.select name="is_vat">
                                    <option value="" disabled>-- Select --</option>
                                    <option value="1" @if($transaction->is_vat == 1) selected @endif>Yes</option>
                                    <option value="0" @if($transaction->is_vat == 0) selected @endif>No</option>
                                </x-form.select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="input-block mb-3">
                                <x-form.label>{{ __('Business Location') }}</x-form.label>
                                <x-form.select name="location_id">
                                    <option value="" disabled>-- Select Location --</option>
                                    @foreach ($business_locations as $key => $location)
                                        <option value="{{ $key }}" @if($transaction->location_id == $key) selected @endif>{{ $location }}</option>
                                    @endforeach
                                </x-form.select>
                            </div>
                        </div>
                         <div class="col-md-4">
                            <div class="input-block mb-3">
                                <x-form.label>{{ __('Store') }}</x-form.label>
                                <x-form.select name="store_id">
                                    <option value="" disabled>-- Select Store --</option>
                                    @foreach ($stores as $key => $store)
                                        <option value="{{ $key }}" @if($transaction->store_id == $key) selected @endif>{{ $store }}</option>
                                    @endforeach
                                </x-form.select>
                            </div>
                        </div>
                        
                         <div class="col-sm-4">
                            <x-form.input-block>
                                <x-form.label>{{ __('Pay term') }}</x-form.label>
                                <div class="d-flex align-items-center gap-0">
                                    <x-form.input type="number" name="pay_term_number" min="1"
                                        placeholder="Enter pay term" value="{{ $transaction->pay_term_number }}" />
                                    <x-form.select name="pay_term_type">
                                        <option value="" disabled>-- Unit --</option>
                                        <option value="months" @if($transaction->pay_term_type == 'months') selected @endif>Months</option>
                                        <option value="days" @if($transaction->pay_term_type == 'days') selected @endif>Days</option>
                                    </x-form.select>
                                </div>
                            </x-form.input-block>
                        </div>
                        
                         <div class="col-sm-8">
                            <x-form.label class="col-form-label">{{ __('Attach Document') }}</x-form.label>
                            <x-form.input type="file" name="document" />
                            @if($transaction->document)
                                <small><a href="{{ asset($transaction->document) }}" target="_blank">View Current Document</a></small>
                            @endif
                        </div>

                        <div class="form-group mb-4 product-search-container">
                             <x-form.label>{{ __('Product / Service') }}</x-form.label>
                            <div class="product-search-input-group">
                                <input type="text" id="product-filter" class="form-control"
                                    placeholder="Search product by name or code">
                            </div>
                            <select id="product-select" size="8">
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
                                    @foreach($transaction->purchase_lines as $purchase_line)
                                        @include('purchase::partials.purchase_entry_row', [
                                            'product' => $purchase_line->product,
                                            'variation' => $purchase_line->variations,
                                            'row_count' => $loop->index,
                                            'taxes' => $taxes,
                                            'currency_details' => $currency_details,
                                            'purchase_line' => $purchase_line
                                        ])
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <hr />
                        <div class="row mb-3">
                             <div class="col-md-6"></div>
                            <div class="col-md-6 text-end">
                                <h5 class="mb-0">
                                    <strong>{{ __('Purchase Total:') }} <span
                                            id="display-grand-total">{{ @num_format($transaction->total_before_tax) }}</span></strong>
                                    <input type="hidden" id="display-grand-total" value="{{ $transaction->total_before_tax }}" name="total_before_tax">
                                </h5>
                            </div>
                        </div>
                         <div class="row mb-3">
                            <div class="col-md-6"></div>
                            <div class="col-md-6 text-end">
                                <h5 class="mb-0">
                                    <strong>{{ __('Discount:') }} <span id="display-grand-total">{{ @num_format($transaction->discount_amount) }}</span></strong>
                                    <input type="hidden" id="discount_amount" value="{{ $transaction->discount_amount }}" name="discount_amount">
                                </h5>
                            </div>
                        </div>
                         <div class="row mb-3">
                            <div class="col-md-6"></div>
                            <div class="col-md-6 text-end">
                                <h5 class="mb-0">
                                    <strong>{{ __('Net Total:') }} <span id="display-grand-total">{{ @num_format($transaction->final_total) }}</span></strong>
                                    <input type="hidden" id="final_total" value="{{ $transaction->final_total }}" name="final_total">
                                </h5>
                            </div>
                        </div>

                        <div class="col-md-12">
                             <div class="input-block mb-3">
                                <x-form.label>{{ __('Additional note') }}</x-form.label>
                                <x-form.textarea name="additional_notes">{{ $transaction->additional_notes }}</x-form.textarea>
                            </div>
                        </div>

                        <!-- Payment Methods Section -->
                        <div class="col-md-12 mb-4">
                             <h5 class="mb-0">{{ __('Payment Details') }}</h5>
                             <div id="payment_rows_div">
                                @foreach($transaction->payments as $payment)
                                    @include('purchase::partials.payment_row', ['payment' => $payment, 'row_index' => $loop->index, 'payment_types' => $payment_types, 'accounts' => $accounts])
                                @endforeach
                             </div>
                             <button type="button" class="btn btn-primary btn-block mt-3" id="add-payment-row">
                                <i class="fa fa-plus"></i> {{ __('Add Payment Method') }}
                             </button>
                        </div>

                    </div>
                </div>
            </div>
            <div class="submit-section">
                <x-form.button class="btn btn-primary submit-btn">{{ __('Update') }}</x-form.button>
            </div>
        </form>
    </div>
@endsection

@push('page-script')
    @if(!isset($jquery_loaded))
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        @php $jquery_loaded = true; @endphp
    @endif
    <script src="{{ asset('modules/purchase/assets/js/purchase.js') }}"></script>
    <script>
        $(document).ready(function() {
            updateGrandTotal(); 
        });
    </script>
@endpush
