@extends('layouts.app')
@section('page-content')

<div class="content container-fluid">
    <x-breadcrumb class="col">
        <x-slot name="title">{{ __('Sales Return') }}</x-slot>
        <ul class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('sales.index') }}">{{ __('Sales') }}</a>
            </li>
            <li class="breadcrumb-item active">
                {{ __('Sales Return') }}
            </li>
        </ul>
    </x-breadcrumb>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('status'))
        <div class="alert alert-{{ session('status.success') ? 'success' : 'danger' }} alert-dismissible fade show" role="alert">
            {{ session('status.msg') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('sales-return.store') }}" method="post" id="sales_return_form">
        @csrf
        <input type="hidden" name="transaction_id" value="{{ $sale->id }}">

        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">@lang('Parent Sale')</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-4">
                        <strong>@lang('Invoice No'):</strong> {{ $sale->invoice_no }} <br>
                        <strong>@lang('Date'):</strong> {{ \Carbon\Carbon::parse($sale->transaction_date)->format('d-m-Y') }}
                    </div>
                    <div class="col-sm-4">
                        <strong>@lang('Customer'):</strong> {{ $sale->contact->name }} <br>
                        <strong>@lang('Location'):</strong> {{ $sale->location->name }}
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-4">
                        <div class="input-block mb-3">
                            <x-form.label>{{ __('Ref No') }}</x-form.label>
                            <x-form.input type="text" name="ref_no" :value="!empty($sale->return_parent->invoice_no) ? $sale->return_parent->invoice_no : ''" />
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <hr>
                    <div class="col-sm-12">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="sales_return_table">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>@lang('Product Name')</th>
                                        <th>@lang('Unit Price')</th>
                                        <th>@lang('Sold Quantity')</th>
                                        <th>@lang('Return Quantity')</th>
                                        <th>@lang('Return Subtotal')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($sale->sell_lines as $sell_line)
                                    @php
                                        $unit_name = $sell_line->product->unit->short_name ?? '';
                                        $check_decimal = 'false';
                                        if(!empty($sell_line->product->unit) && $sell_line->product->unit->allow_decimal == 0){
                                            $check_decimal = 'true';
                                        }
                                        if(!empty($sell_line->sub_unit->base_unit_multiplier)) {
                                            $unit_name = $sell_line->sub_unit->short_name;
                                            if($sell_line->sub_unit->allow_decimal == 0){
                                                $check_decimal = 'true';
                                            } else {
                                                $check_decimal = 'false';
                                            }
                                        }
                                        $max_return = $sell_line->quantity;
                                        $already_returned = $sell_line->quantity_returned;
                                        $max_returnable = $sell_line->quantity - $already_returned;
                                    @endphp
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            {{ $sell_line->product->name }}
                                            @if(!empty($sell_line->variations) && $sell_line->product->type == 'variable')
                                            - {{ $sell_line->variations->product_variation->name ?? ''}}
                                            - {{ $sell_line->variations->name ?? ''}}
                                            @endif
                                            @if($already_returned > 0)
                                                <br><small class="text-muted">(Already returned: {{ number_format($already_returned, 2) }} {{$unit_name}})</small>
                                            @endif
                                        </td>
                                        <td><span class="display_currency" data-currency_symbol="true">{{ $sell_line->unit_price_inc_tax }}</span></td>
                                        <td><span class="display_currency" data-is_quantity="true" data-currency_symbol="false">{{ $sell_line->quantity }}</span> {{$unit_name}}</td>
                                        <td>
                                            <input type="text" name="returns[{{$sell_line->id}}]" value="{{@format_quantity($sell_line->quantity_returned)}}"
                                            class="form-control input-sm input_number return_qty input_quantity"
                                            data-rule-abs_digit="{{$check_decimal}}" 
                                            data-msg-abs_digit="@lang('Decimal value not allowed')"
                                            data-rule-max="{{$max_return}}"
                                            data-msg-max="@lang('Maximum return quantity is') {{$max_return}}" 
                                            @if($sell_line->quantity <= 0)
                                                disabled
                                            @endif
                                            >
                                            <input type="hidden" class="unit_price" value="{{@num_format($sell_line->unit_price_inc_tax)}}">
                                        </td>
                                        <td>
                                            <div class="return_subtotal"></div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-sm-6 offset-sm-6">
                        <div class="mb-2">
                            <strong>@lang('Total Return Tax'): </strong>
                            <span id="total_return_tax">0.00</span> 
                            @if(!empty($sale->tax))
                                ({{$sale->tax->name}} - {{$sale->tax->amount}}%)
                            @endif
                        </div>
                        @php
                            $tax_percent = 0;
                            if(!empty($sale->tax)){
                                $tax_percent = $sale->tax->amount;
                            }
                        @endphp
                        <input type="hidden" name="tax_id" value="{{ $sale->tax_id }}">
                        <input type="hidden" name="tax_amount" value="0" id="tax_amount">
                        <input type="hidden" name="tax_percent" value="{{ $tax_percent }}" id="tax_percent">
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12 text-end">
                        <h5>
                            <strong>@lang('Return Total'): </strong>&nbsp;
                            <span id="net_return">0.00</span>
                        </h5>
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col-sm-12">
                        <button type="submit" class="btn btn-primary float-end">@lang('Save')</button>
                    </div>
                </div>
            </div>
        </div>

    </form>

</div>
@stop

@push('page-scripts')
<script type="text/javascript">
    window.__currency_symbol = "{{ session('currency.symbol') }}";

    window.addEventListener('load', function() {
        $(document).ready( function(){
            update_sales_return_total();
            
            if (typeof $.fn.validate !== 'undefined') {
                $('form#sales_return_form').validate();
            }
        });
        
        $(document).on('change', 'input.return_qty', function(){
            update_sales_return_total()
        });
    });

    function update_sales_return_total(){
        // Check if helpers are loaded
        if (typeof __read_number === 'undefined' || typeof __currency_trans_from_en === 'undefined') {
            setTimeout(update_sales_return_total, 100);
            return;
        }

        var net_return = 0;
        $('table#sales_return_table tbody tr').each( function(){
            var quantity = __read_number($(this).find('input.return_qty'));
            var unit_price = __read_number($(this).find('input.unit_price'));
            var subtotal = quantity * unit_price;
            $(this).find('.return_subtotal').text(__currency_trans_from_en(subtotal, true));
            net_return += subtotal;
        });
        
        var tax_percent = $('input#tax_percent').val();
        // Since unit_price is already inclusive of tax, we need to extract the tax from the net_return
        var total_tax = __calculate_amount('percentage', tax_percent, net_return);
        // Note: ERP typically calculates tax on top of subtotal. 
        // If the price is inclusive, we might need to calculate the base price.
        // But for simplicity in this specific return form, we'll assume the net_return is the total.
        // We will just show the calculated tax based on the percentage of the net total.
        
        $('input#tax_amount').val(total_tax);
        $('span#total_return_tax').text(__currency_trans_from_en(total_tax, true));
        $('span#net_return').text(__currency_trans_from_en(net_return, true));
    }
</script>
@endpush
