<tr class="product_row">
    <td>
        {{$product->product_name}}
        <br/>
        {{$product->sub_sku}}

        @if( session()->get('business.enable_lot_number') == 1 || session()->get('business.enable_product_expiry') == 1)
        @php
            $lot_enabled = session()->get('business.enable_lot_number');
            $exp_enabled = session()->get('business.enable_product_expiry');
            $lot_no_line_id = '';
            if(!empty($product->lot_no_line_id)){
                $lot_no_line_id = $product->lot_no_line_id;
            }
        @endphp

        @if($product->enable_stock == 1)
            <br>
            <small class="text-muted" style="white-space: nowrap;">Current Stock: <span class="qty_available_text">{{$product->formatted_qty_available}}</span> {{ $product->unit }}</small>
        @endif
        @if(!empty($product->lot_numbers))
            <select class="form-control lot_number" name="products[{{$row_index}}][lot_no_line_id]">
                <option value="">Lot & Expiry</option>
                @foreach($product->lot_numbers as $lot_number)
                    @php
                        $selected = "";
                        if($lot_number->purchase_line_id == $lot_no_line_id){
                            $selected = "selected";

                            $max_qty_rule = $lot_number->qty_available;
                            $max_qty_msg = 'Quantity error: ' . $lot_number->qty_formated . ' ' . $product->unit . ' available in this lot';
                        }

                        $expiry_text = '';
                        if($exp_enabled == 1 && !empty($lot_number->exp_date)){
                            if( \Carbon\Carbon::now()->gt(\Carbon\Carbon::createFromFormat('Y-m-d', $lot_number->exp_date)) ){
                                $expiry_text = '(Expired)';
                            }
                        }
                    @endphp
                    <option value="{{$lot_number->purchase_line_id}}" data-qty_available="{{$lot_number->qty_available}}" data-msg-max="{{ 'Quantity error: ' . $lot_number->qty_formated . ' ' . $product->unit . ' available in this lot' }}" {{$selected}}>@if(!empty($lot_number->lot_number) && $lot_enabled == 1){{$lot_number->lot_number}} @endif @if($lot_enabled == 1 && $exp_enabled == 1) - @endif @if($exp_enabled == 1 && !empty($lot_number->exp_date)) Exp. Date: {{ format_date($lot_number->exp_date) }} @endif {{$expiry_text}}</option>
                @endforeach
            </select>
        @endif
    @endif
    </td>
    <td>
        <span class="qty_available_text">{{$product->formatted_qty_available}}</span> {{ $product->unit }}
    </td>
    <td>
        {{-- If edit then transaction sell lines will be present --}}
        @if(!empty($product->transaction_sell_lines_id))
            <input type="hidden" name="products[{{$row_index}}][transaction_sell_lines_id]" class="form-control" value="{{$product->transaction_sell_lines_id}}">
        @endif

        <input type="hidden" name="products[{{$row_index}}][product_id]" class="form-control product_id" value="{{$product->product_id}}">

        <input type="hidden" value="{{$product->variation_id}}" 
            name="products[{{$row_index}}][variation_id]">

        <input type="hidden" value="{{$product->enable_stock}}" 
            name="products[{{$row_index}}][enable_stock]">
        
        @if(empty($product->quantity_ordered))
            @php
                $product->quantity_ordered = 1;
            @endphp
        @endif

       <input type="text" 
               class="form-control product_quantity input_number input_quantity" 
               value="{{ format_quantity($product->quantity_ordered) }}" 
               name="products[{{ $row_index }}][quantity]" 
               @if($product->unit_allow_decimal == 1) 
                   data-decimal=1 
               @else 
                   data-rule-abs_digit="true" 
                   data-msg-abs_digit="Decimal value not allowed" 
                   data-decimal=0 
               @endif
               data-rule-required="true" 
               data-msg-required="This field is required" 
        >


        {{$product->unit}}
    </td>
    <td>
        <input type="text" name="products[{{$row_index}}][unit_price]" class="form-control product_unit_price input_number" value="{{ num_format($product->last_purchased_price) }}">
    </td>
    <td>
        <input type="text" readonly name="products[{{$row_index}}][price]" class="form-control product_line_total" value="{{ num_format($product->quantity_ordered*$product->last_purchased_price) }}">
    </td>
    <td class="text-center">
        <i class="fa fa-trash remove_product_row cursor-pointer" aria-hidden="true"></i>
    </td>
</tr>
