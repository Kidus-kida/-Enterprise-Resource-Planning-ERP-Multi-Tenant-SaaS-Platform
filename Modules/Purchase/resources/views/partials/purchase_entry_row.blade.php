@foreach($variations as $variation)
    <tr class="purchase_entry_row">
        <td class="text-center">
            <span class="sr_number"></span>
        </td>
        
        <td>
            <strong>{{ $product->name }}</strong>
            @if($variation && $variation->name != 'DUMMY')
                <br><small>{{ $variation->name }}</small>
            @endif
            <br><small class="text-muted">({{ $variation->sub_sku ?? $product->sku }})</small>
            
            {{-- Hidden inputs --}}
            <input type="hidden" name="purchases[{{ $row_count }}][product_id]" value="{{ $product->id }}">
            <input type="hidden" name="purchases[{{ $row_count }}][variation_id]" value="{{ $variation->id }}" class="hidden_variation_id">
            <input type="hidden" name="purchases[{{ $row_count }}][row_count]" value="{{ $row_count }}">
            <input type="hidden" name="purchases[{{ $row_count }}][product_unit_id]" value="{{ $product->unit->id }}">
        </td>
        
        <td>
            {{-- Purchase Quantity --}}
            @php
                $check_decimal = 'false';
                if($product->unit->allow_decimal == 0){
                    $check_decimal = 'true';
                }
            @endphp
            <x-form.input type="number" 
                   name="purchases[{{ $row_count }}][quantity]" 
                   value="1"
                   min="1"
                   max={{$current_stock}}
                   class="form-control purchase_quantity" 
                   required />
                   
            @if(!empty($sub_units))
                <br>
                <select name="purchases[{{$row_count}}][sub_unit_id]" class="form-control input-sm sub_unit">
                    @foreach($sub_units as $key => $value)
                        <option value="{{$key}}" data-multiplier="{{$value['multiplier']}}">
                            {{$value['name']}}
                        </option>
                    @endforeach
                </select>
            @else
                @if($product->unit)
                    <span class="unit_text">{{ $product->unit->short_name }}</span>
                @endif
            @endif

            @php
                $business_id = request()->session()->get('user.business_id') ?? 1;
                //$enable_free_qty = App\Business::where('id', $business_id)->select('enable_free_qty')->first()->enable_free_qty;
                $enable_free_qty = []; //--- TEMPORARY FIX ---
            @endphp

            @if ($enable_free_qty)
                <br>
                <input type="number" name="purchases[{{$row_count}}][free_qty]"
                    class="free_qty form-control" placeholder="@lang( 'Free Qty' )" value="">
            @endif
        </td>
        
        <td>
            {{-- Available Quantity --}}
            <input type="text" 
                   class="form-control current_stock bg-light" 
                   value="{{ number_format($current_stock, 2) }}" 
                   readonly
                   data-orignalstock="{{ $current_stock }}">
        </td>
        
        <td>
            {{-- Unit Cost Before Discount --}}
            <input type="number" 
                   name="purchases[{{ $row_count }}][pp_without_discount]" 
                   class="form-control purchase_unit_cost_without_discount" 
                   value="{{ $variation->default_purchase_price }}" 
                   step="0.01" 
                   min="0">
        </td>
        
        <td>
            {{-- Discount Percent --}}
            <input type="number" 
                   name="purchases[{{ $row_count }}][discount_percent]" 
                   class="form-control inline_discounts" 
                   value="0" 
                   step="0.01" 
                   min="0"
                   max="100">
        </td>
        
        <td>
            {{-- Unit Cost Before Tax --}}
            <input type="number" 
                   name="purchases[{{ $row_count }}][purchase_price]" 
                   class="form-control purchase_unit_cost" 
                   value="{{ $variation->default_purchase_price }}" 
                   step="0.01" 
                   min="0">
        </td>
        
        <td>
            {{-- Subtotal Before Tax --}}
            <span class="row_subtotal_before_tax display_currency">{{ number_format($variation->default_purchase_price, 2) }}</span>
            <input type="hidden" 
                   name="purchases[{{ $row_count }}][subtotal_before_tax]" 
                   class="row_subtotal_before_tax_hidden" 
                   value="{{ $variation->default_purchase_price }}">
        </td>
        
        <td>
            {{-- Product Tax --}}
            <select name="purchases[{{ $row_count }}][purchase_line_tax_id]" class="form-select purchase_line_tax_id" {{ $hide_tax == 'hide' ? 'hidden' : '' }}>
                <option value="" data-tax_amount="0">None</option>
                @foreach($taxes as $tax)
                    <option value="{{ $tax->id }}" 
                            data-tax_amount="{{ $tax->amount }}"
                            @if($product->tax == $tax->id) selected @endif>
                        {{ $tax->name }} ({{ $tax->amount }}%)
                    </option>
                @endforeach
            </select>
            @if($hide_tax == 'hide')
                <br>
                <small class="purchase_product_unit_tax_text">0.00</small>
            @endif
            <input type="hidden" name="purchases[{{ $row_count }}][item_tax]" class="purchase_product_unit_tax" value="0">
        </td>
        
        <td>
            {{-- Net Cost (Unit Cost After Tax) --}}
            @php
                $dpp_inc_tax = $variation->dpp_inc_tax;
                if($hide_tax == 'hide'){
                    $dpp_inc_tax = $variation->default_purchase_price;
                }
            @endphp
            <input type="number" 
                   name="purchases[{{ $row_count }}][purchase_price_inc_tax]" 
                   class="form-control purchase_unit_cost_after_tax" 
                   value="{{ $dpp_inc_tax }}" 
                   step="0.01" 
                   min="0">
        </td>
        
        <td>
            {{-- Line Total --}}
            <span class="row_subtotal_after_tax display_currency">{{ number_format($dpp_inc_tax, 2) }}</span>
            <input type="hidden" 
                   name="purchases[{{ $row_count }}][subtotal]" 
                   class="row_subtotal_after_tax_hidden" 
                   value="{{ $dpp_inc_tax }}">
        </td>
        
        <td>
            {{-- Profit Margin % --}}
            <input type="number" 
                   name="purchases[{{ $row_count }}][profit_percent]" 
                   class="form-control profit_percent" 
                   value="{{ $variation->profit_percent ?? 0 }}" 
                   step="0.01" 
                   min="0">
        </td>
        
        <td>
            {{-- Unit Selling Price (Exc. tax) --}}
            <input type="number" 
                   name="purchases[{{ $row_count }}][default_sell_price]" 
                   class="form-control default_sell_price" 
                   value="{{ $variation->default_sell_price }}" 
                   step="0.01" 
                   min="0">
        </td>
        
        <td class="text-center">
            {{-- Delete Button --}}
            <button type="button" 
                    class="btn btn-sm btn-danger remove_purchase_entry_row" 
                    data-row_count="{{ $row_count }}">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    </tr>
    
    @if($enable_lot_number || $enable_product_expiry)
    <tr class="lot_number_row hide" data-row="{{ $row_count }}">
        <td colspan="14">
            <div class="row">
                @if($enable_lot_number)
                <div class="col-md-4">
                    <label>Lot Number:</label>
                    <input type="text" 
                           name="purchases[{{ $row_count }}][lot_number]" 
                           class="form-control">
                </div>
                @endif
                
                @if($enable_product_expiry)
                <div class="col-md-4">
                    <label>MFG Date:</label>
                    <input type="date" 
                           name="purchases[{{ $row_count }}][mfg_date]" 
                           class="form-control mfg_date">
                </div>
                
                <div class="col-md-4">
                    <label>EXP Date:</label>
                    <input type="date" 
                           name="purchases[{{ $row_count }}][exp_date]" 
                           class="form-control exp_date">
                </div>
                @endif
            </div>
        </td>
    </tr>
    @endif

    @php $row_count++; @endphp
@endforeach

<input type="hidden" id="row_count" value="{{ $row_count }}">
