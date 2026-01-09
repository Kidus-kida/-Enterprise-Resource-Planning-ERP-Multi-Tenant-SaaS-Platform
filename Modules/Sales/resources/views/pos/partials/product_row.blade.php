@php
    $row_index = $row_index ?? (time() . rand(1, 100));
    
    // If we have a sell_line (edit mode), override product/variation defaults
    if (!empty($sell_line)) {
        $product = $sell_line->product;
        $variation = $sell_line->variations;
        // Map sell_line properties to what the row expects
        $quantity = $sell_line->quantity;
        $unit_price = $sell_line->unit_price;
        $unit_price_inc_tax = $sell_line->unit_price_inc_tax;
        $line_tax_id = $sell_line->tax_id;
        $line_discount_amount = $sell_line->line_discount_amount;
        $line_discount_type = $sell_line->line_discount_type;
        $sub_unit_id = $sell_line->sub_unit_id;
        
        // ProductUtil needed for sub_units if we're in Blade
        // or we can pass sub_units from controller
    } else {
        $quantity = 1;
        $unit_price = $variation->default_sell_price;
        $unit_price_inc_tax = $variation->default_sell_price;
        $line_tax_id = $product->tax_id;
        $line_discount_amount = 0;
        $line_discount_type = 'fixed';
        $sub_unit_id = null;
    }
@endphp
<tr data-variation-id="{{ $variation->id }}">
    <td>
        <div class="product-name-cell">
            <strong>{{ $product->product_name ?? $product->name }}</strong>
            @if($variation->name != 'DUMMY')
                <br><small class="text-muted">{{ $variation->name }}</small>
            @endif
            <input type="hidden" name="products[{{ $row_index }}][product_id]" value="{{ $product->id }}">
            <input type="hidden" name="products[{{ $row_index }}][variation_id]" value="{{ $variation->id }}">
            <input type="hidden" name="products[{{ $row_index }}][enable_stock]" value="{{ $product->enable_stock }}">
            <input type="hidden" name="products[{{ $row_index }}][unit_price]" class="unit_price_exc_tax" value="{{ $unit_price }}" data-default-price="{{ $variation->default_sell_price }}">
            <input type="hidden" name="products[{{ $row_index }}][tax_id]" value="{{ $line_tax_id }}">
            <input type="hidden" name="products[{{ $row_index }}][item_tax]" value="{{ $sell_line->item_tax ?? 0 }}">
            <input type="hidden" name="products[{{ $row_index }}][line_discount_amount]" value="{{ $line_discount_amount }}">
            <input type="hidden" name="products[{{ $row_index }}][line_discount_type]" value="{{ $line_discount_type }}">
            <input type="hidden" name="products[{{ $row_index }}][product_unit_id]" value="{{ $product->unit_id }}">
            <input type="hidden" name="products[{{ $row_index }}][base_unit_multiplier]" class="base_unit_multiplier" value="{{ $sell_line->base_unit_multiplier ?? 1 }}">
            <input type="hidden" class="max_qty_available" value="{{ $product->current_stock ?? 0 }}">
            @if(!empty($sell_line))
                <input type="hidden" name="products[{{ $row_index }}][transaction_sell_lines_id]" value="{{ $sell_line->id }}">
            @endif
        </div>
    </td>
    <td>
        <div class="qty-input-group">
            <button type="button" class="btn btn-outline-secondary btn-qty btn-qty-minus"><i class="fa fa-minus"></i></button>
            <input type="number" name="products[{{ $row_index }}][quantity]" class="form-control qty-input" value="{{ $quantity }}" min="1">
            <button type="button" class="btn btn-outline-secondary btn-qty btn-qty-plus"><i class="fa fa-plus"></i></button>
        </div>
        
        @if(!empty($sub_units))
            <select name="products[{{ $row_index }}][sub_unit_id]" class="form-select form-select-sm mt-1 sub_unit">
                @foreach($sub_units as $key => $value)
                    <option value="{{$key}}" data-multiplier="{{$value['multiplier']}}" @if($sub_unit_id == $key) selected @endif>{{$value['name']}}</option>
                @endforeach
            </select>
        @else 
            @if(!empty($product->unit->actual_name))
                <div class="text-center text-muted small mt-1">{{$product->unit->actual_name}}</div>
            @elseif(!empty($product->unit))
                <div class="text-center text-muted small mt-1">{{ is_string($product->unit) ? $product->unit : ($product->unit->short_name ?? '') }}</div>
            @endif
        @endif
    </td>
    <td class="text-end">
        <span class="row-unit-price">{{ number_format($unit_price_inc_tax, 2) }}</span>
        <input type="hidden" name="products[{{ $row_index }}][unit_price_inc_tax]" class="unit_price_hidden" value="{{ $unit_price_inc_tax }}" data-default-price="{{ $variation->default_sell_price }}">
    </td>
    <td class="text-end">
        <span class="pos-subtotal-text">{{ number_format($unit_price_inc_tax * $quantity, 2) }}</span>
    </td>
    <td class="text-center">
        <button type="button" class="btn btn-link text-danger remove-pos-row"><i class="fa fa-times"></i></button>
    </td>
</tr>
