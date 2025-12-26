<tr class="sell_entry_row">
    <td class="text-center">
        <span class="sr_number"></span>
    </td>
    
    <td>
        <strong>{{ $product->name }}</strong>
        @if($variation && $variation->name != 'DUMMY')
            <br><small>{{ $variation->name }}</small>
        @endif
        <br><small class="text-muted">({{ $variation->sub_sku ?? $product->sku }})</small>
        
        {{-- Hidden inputs for TransactionUtil --}}
        <input type="hidden" name="sales[{{ $row_count }}][product_id]" value="{{ $product->id }}">
        <input type="hidden" name="sales[{{ $row_count }}][variation_id]" value="{{ $variation->id }}" class="hidden_variation_id">
        <input type="hidden" name="sales[{{ $row_count }}][row_count]" value="{{ $row_count }}">
        <input type="hidden" name="sales[{{ $row_count }}][product_unit_id]" value="{{ $product->unit->id }}">
        <input type="hidden" name="sales[{{ $row_count }}][line_discount_type]" value="percentage">
        <input type="hidden" name="sales[{{ $row_count }}][enable_stock]" value="{{ $product->enable_stock }}">
        <input type="hidden" name="sales[{{ $row_count }}][product_type]" value="{{ $product->type }}">
        <input type="hidden" name="sales[{{ $row_count }}][base_unit_multiplier]" value="1">
        
        {{-- Fields needed by TransactionUtil::createOrUpdateSellLines --}}
        <input type="hidden" name="sales[{{ $row_count }}][unit_price_inc_tax]" class="sell_line_unit_price_inc_tax" value="{{ $variation->sell_price_inc_tax ?? $variation->default_sell_price }}">
    </td>
    
    <td>
        {{-- Quantity --}}
        <input type="number" 
               name="sales[{{ $row_count }}][quantity]" 
               value="1"
               min="1"
               class="form-control sell_quantity" 
               required />
        @if($product->unit)
            <span class="unit_text">{{ $product->unit->short_name }}</span>
        @endif
    </td>
    
    <td>
        {{-- Available Quantity --}}
        <input type="text" 
               class="form-control bg-light" 
               value="{{ number_format($current_stock, 2) }}" 
               readonly>
    </td>
    
    <td>
        {{-- Unit Price (Exc. Tax) --}}
        <input type="number" 
               name="sales[{{ $row_count }}][unit_price]" 
               class="form-control sell_unit_price" 
               value="{{ number_format($variation->default_sell_price, 2, '.', '') }}" 
               step="0.01" 
               min="0">
    </td>
    
    <td>
        {{-- Discount Percent --}}
        <input type="number" 
               name="sales[{{ $row_count }}][line_discount_amount]" 
               class="form-control sell_line_discount" 
               value="0" 
               step="0.01" 
               min="0"
               max="100">
    </td>
    
    <td>
        {{-- Subtotal Before Tax --}}
        <span class="row_subtotal_before_tax">{{ number_format($variation->default_sell_price, 2) }}</span>
        <input type="hidden" 
               name="sales[{{ $row_count }}][subtotal_before_tax]" 
               class="row_subtotal_before_tax_hidden" 
               value="{{ $variation->default_sell_price }}">
    </td>
    
    <td>
        {{-- Product Tax --}}
        <select name="sales[{{ $row_count }}][tax_id]" class="form-select sell_line_tax_id">
            <option value="" data-tax_amount="0">{{ __('None') }}</option>
            @foreach($taxes as $tax)
                <option value="{{ $tax->id }}" 
                        data-tax_amount="{{ $tax->amount }}"
                        @if($product->tax == $tax->id) selected @endif>
                    {{ $tax->name }} ({{ $tax->amount }}%)
                </option>
            @endforeach
        </select>
        <input type="hidden" name="sales[{{ $row_count }}][item_tax]" class="sell_line_tax_amount" value="0">
    </td>
    
    <td>
        {{-- Line Total --}}
        <span class="row_line_total">{{ number_format($variation->sell_price_inc_tax ?? $variation->default_sell_price, 2) }}</span>
        <input type="hidden" 
               name="sales[{{ $row_count }}][line_total]" 
               class="row_line_total_hidden" 
               value="{{ $variation->sell_price_inc_tax ?? $variation->default_sell_price }}">
    </td>
    
    <td class="text-center">
        {{-- Delete Button --}}
        <button type="button" 
                class="btn btn-sm btn-danger remove_sell_entry_row" 
                data-row_count="{{ $row_count }}">
            <i class="fas fa-trash"></i>
        </button>
    </td>
</tr>
