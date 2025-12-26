@php
    $row_index = time() . rand(1, 100);
@endphp
<tr data-variation-id="{{ $variation->id }}">
    <td>
        <div class="product-name-cell">
            <strong>{{ $product->product_name }}</strong>
            @if($variation->name != 'DUMMY')
                <br><small class="text-muted">{{ $variation->name }}</small>
            @endif
            <input type="hidden" name="products[{{ $row_index }}][product_id]" value="{{ $product->product_id }}">
            <input type="hidden" name="products[{{ $row_index }}][variation_id]" value="{{ $variation->id }}">
            <input type="hidden" name="products[{{ $row_index }}][enable_stock]" value="{{ $product->enable_stock }}">
            <input type="hidden" name="products[{{ $row_index }}][unit_price_inc_tax]" class="unit_price_hidden" value="{{ $product->sell_price_inc_tax }}">
        </div>
    </td>
    <td>
        <div class="qty-input-group">
            <button type="button" class="btn btn-outline-secondary btn-qty btn-qty-minus"><i class="fa fa-minus"></i></button>
            <input type="number" name="products[{{ $row_index }}][quantity]" class="form-control qty-input" value="1" min="1">
            <button type="button" class="btn btn-outline-secondary btn-qty btn-qty-plus"><i class="fa fa-plus"></i></button>
        </div>
    </td>
    <td class="text-end">
        <span class="pos-subtotal-text">{{ number_format($product->sell_price_inc_tax, 2) }}</span>
    </td>
    <td class="text-center">
        <button type="button" class="btn btn-link text-danger remove-pos-row"><i class="fa fa-trash-o fa-lg"></i></button>
    </td>
</tr>
