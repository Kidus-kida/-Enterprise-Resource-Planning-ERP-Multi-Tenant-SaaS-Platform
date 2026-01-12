@extends('layouts.app')
@section('page-content')

<div class="content container-fluid">
    <x-breadcrumb class="col">
        <x-slot name="title">{{ __('Purchase Return') }}</x-slot>
        <ul class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('purchase.index') }}">{{ __('Purchases') }}</a>
            </li>
            <li class="breadcrumb-item active">
                {{ __('Purchase Return') }}
            </li>
        </ul>
    </x-breadcrumb>

    <form action="{{ route('purchase-return.store') }}" method="post" id="purchase_return_form">
        @csrf
        <input type="hidden" name="transaction_id" value="{{ $purchase->id }}">

        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">@lang('Parent Purchase')</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-4">
                        <strong>@lang('Ref No'):</strong> {{ $purchase->ref_no }} <br>
                        <strong>@lang('Date'):</strong> {{$purchase->transaction_date->format('d-m-Y')}}
                    </div>
                    <div class="col-sm-4">
                        <strong>@lang('Supplier'):</strong> {{ $purchase->contact->name }} <br>
                        <strong>@lang('Location'):</strong> {{ $purchase->location->name }}
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
                            <x-form.input type="text" name="ref_no" :value="!empty($purchase->return_parent->ref_no) ? $purchase->return_parent->ref_no : ''" />
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <hr>
                    <div class="col-sm-12">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="purchase_return_table">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>@lang('Product Name')</th>
                                        <th>@lang('Unit Price')</th>
                                        <th>@lang('Purchase Quantity')</th>
                                        <th>@lang('Quantity Left')</th>
                                        <th>@lang('Return Quantity')</th>
                                        <th>@lang('Return Subtotal')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($purchase->purchase_lines as $purchase_line)
                                    @php
                                        $unit_name = $purchase_line->product->unit->short_name;
                                        $check_decimal = 'false';
                                        if($purchase_line->product->unit->allow_decimal == 0){
                                            $check_decimal = 'true';
                                        }
                                        if(!empty($purchase_line->sub_unit->base_unit_multiplier)) {
                                            $unit_name = $purchase_line->sub_unit->short_name;
                                            if($purchase_line->sub_unit->allow_decimal == 0){
                                                $check_decimal = 'true';
                                            } else {
                                                $check_decimal = 'false';
                                            }
                                        }
                                        $qty_available = $purchase_line->quantity - $purchase_line->quantity_returned - $purchase_line->quantity_sold - $purchase_line->quantity_adjusted;
                                        $max_return = $qty_available + $purchase_line->quantity_returned;
                                    @endphp
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            {{ $purchase_line->product->name }}
                                            @if( $purchase_line->product->type == 'variable')
                                            - {{ $purchase_line->variations->product_variation->name}}
                                            - {{ $purchase_line->variations->name}}
                                            @endif
                                        </td>
                                        <td><span class="display_currency" data-currency_symbol="true">{{ $purchase_line->purchase_price_inc_tax }}</span></td>
                                        <td><span class="display_currency" data-is_quantity="true" data-currency_symbol="false">{{ $purchase_line->quantity }}</span> {{$unit_name}}</td>
                                        <td><span class="display_currency" data-currency_symbol="false" data-is_quantity="true">{{ $qty_available }}</span> {{$unit_name}}</td>
                                        <td>
                                            <input type="text" name="returns[{{$purchase_line->id}}]" value="{{@format_quantity($purchase_line->quantity_returned)}}"
                                            class="form-control input-sm input_number return_qty input_quantity"
                                            data-rule-abs_digit="{{$check_decimal}}" 
                                            data-msg-abs_digit="@lang('Decimal value not allowed')"
                                            @if($purchase_line->product->enable_stock) 
                                                data-rule-max="{{$max_return}}"
                                                data-msg-max="@lang('Quantity not available')" 
                                            @endif
                                            @if($purchase_line->quantity <= 0)
                                                disabled
                                            @endif
                                            >
                                            <input type="hidden" class="unit_price" value="{{@num_format($purchase_line->purchase_price_inc_tax)}}">
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
                            @if(!empty($purchase->tax))
                                ({{$purchase->tax->name}} - {{$purchase->tax->amount}}%)
                            @endif
                        </div>
                        @php
                            $tax_percent = 0;
                            if(!empty($purchase->tax)){
                                $tax_percent = $purchase->tax->amount;
                            }
                        @endphp
                        <input type="hidden" name="tax_id" value="{{ $purchase->tax_id }}">
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
            update_purchase_return_total();
            
            if (typeof $.fn.validate !== 'undefined') {
                $('form#purchase_return_form').validate();
            }
        });
        
        $(document).on('change', 'input.return_qty', function(){
            update_purchase_return_total()
        });
    });

    function update_purchase_return_total(){
        // Check if helpers are loaded
        if (typeof __read_number === 'undefined' || typeof __currency_trans_from_en === 'undefined') {
            setTimeout(update_purchase_return_total, 100);
            return;
        }

        var net_return = 0;
        $('table#purchase_return_table tbody tr').each( function(){
            var quantity = __read_number($(this).find('input.return_qty'));
            var unit_price = __read_number($(this).find('input.unit_price'));
            var subtotal = quantity * unit_price;
            $(this).find('.return_subtotal').text(__currency_trans_from_en(subtotal, true));
            net_return += subtotal;
        });
        
        var tax_percent = $('input#tax_percent').val();
        var total_tax = __calculate_amount('percentage', tax_percent, net_return);
        var net_return_inc_tax = total_tax + net_return;

        $('input#tax_amount').val(total_tax);
        $('span#total_return_tax').text(__currency_trans_from_en(total_tax, true));
        $('span#net_return').text(__currency_trans_from_en(net_return_inc_tax, true));
    }
</script>
@endpush
