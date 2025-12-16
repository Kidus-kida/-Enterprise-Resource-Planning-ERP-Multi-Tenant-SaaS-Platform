{{-- Purchase Entry Row - Dynamically Added via AJAX --}}
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
    </td>
    
    <td>
        {{-- Purchase Quantity --}}
        <input type="number" 
               name="purchases[{{ $row_count }}][quantity]" 
               class="form-control purchase_quantity" 
               value="1" 
               step="0.01" 
               min="0.01" 
               required>
               
        @if($product->unit)
            <span class="unit_text">{{ $product->unit->short_name }}</span>
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
               name="purchases[{{ $row_count }}][purchase_price_inc_tax]" 
               class="form-control purchase_unit_cost_without_discount" 
               value="{{ $default_purchase_price }}" 
               step="0.01" 
               min="0">
    </td>
    
    <td>
        {{-- Discount Percent --}}
        <input type="number" 
               name="purchases[{{ $row_count }}][line_discount_amount]" 
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
               value="{{ $default_purchase_price_exc_tax }}" 
               step="0.01" 
               min="0">
    </td>
    
    <td>
        {{-- Subtotal Before Tax --}}
        <span class="row_subtotal_before_tax display_currency">{{ number_format($default_purchase_price_exc_tax, 2) }}</span>
        <input type="hidden" 
               name="purchases[{{ $row_count }}][subtotal_before_tax]" 
               class="row_subtotal_before_tax_hidden" 
               value="{{ $default_purchase_price_exc_tax }}">
    </td>
    
    <td>
        {{-- Product Tax --}}
        <select name="purchases[{{ $row_count }}][tax_id]" class="form-select purchase_line_tax_id">
            <option value="" data-tax_amount="0">None</option>
            @foreach($taxes as $tax)
                <option value="{{ $tax->id }}" 
                        data-tax_amount="{{ $tax->amount }}"
                        @if($product->tax_id == $tax->id) selected @endif>
                    {{ $tax->name }} ({{ $tax->amount }}%)
                </option>
            @endforeach
        </select>
        <br>
        <small class="purchase_product_unit_tax_text">0.00</small>
        <input type="hidden" name="purchases[{{ $row_count }}][item_tax]" class="purchase_product_unit_tax" value="0">
    </td>
    
    <td>
        {{-- Net Cost (Unit Cost After Tax) --}}
        <input type="number" 
               name="purchases[{{ $row_count }}][net_cost]" 
               class="form-control purchase_unit_cost_after_tax" 
               value="{{ $default_purchase_price }}" 
               step="0.01" 
               min="0">
    </td>
    
    <td>
        {{-- Line Total --}}
        <span class="row_subtotal_after_tax display_currency">{{ number_format($default_purchase_price, 2) }}</span>
        <input type="hidden" 
               name="purchases[{{ $row_count }}][subtotal]" 
               class="row_subtotal_after_tax_hidden" 
               value="{{ $default_purchase_price }}">
    </td>
    
    <td>
        {{-- Profit Margin % --}}
        <input type="number" 
               name="purchases[{{ $row_count }}][profit_percent]" 
               class="form-control profit_percent" 
               value="0" 
               step="0.01" 
               min="0">
    </td>
    
    <td>
        {{-- Unit Selling Price (Exc. tax) --}}
        <input type="number" 
               name="purchases[{{ $row_count }}][default_sell_price]" 
               class="form-control default_sell_price" 
               value="{{ $variation->sell_price_inc_tax ?? 0 }}" 
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
