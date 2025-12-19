@php 
    $colspan = 15;
    $custom_labels = json_decode(session('business.custom_labels'), true);
@endphp
<table class="table table-bordered table-striped ajax_view " id="product_table" style="width: 100% ">
    
    <thead>
        <tr>
            <td colspan="{{$colspan}}">
            <div style="display: flex; width: 100%;">
                @can('product.delete')
                    <form action="{{ action([\Modules\Products\Http\Controllers\ProductController::class, 'massDestroy']) }}" method="post" id="mass_delete_form">
                        @csrf
                        <input type="hidden" name="selected_rows" id="selected_rows">
                        <button type="submit" class="btn btn-xs btn-danger" id="delete-selected">Delete Selected</button>
                    </form>
                @endcan

                
                    @can('product.update')
                    
                        @if(config('constants.enable_product_bulk_edit'))
                            &nbsp;
                            <form action="{{ action([\Modules\Products\Http\Controllers\ProductController::class, 'bulkEdit']) }}" method="post" id="bulk_edit_form">
                                @csrf
                                <input type="hidden" name="selected_products" id="selected_products_for_edit">
                                <button type="submit" class="btn btn-xs btn-primary" id="edit-selected"> <i class="fa fa-edit"></i>Bulk Edit</button>
                            </form>
                        @endif
                        &nbsp;
                        <button type="button" class="btn btn-xs btn-success update_product_location" data-type="add">Add to Location</button>
                        &nbsp;
                        <button type="button" class="btn btn-xs bg-navy update_product_location" data-type="remove">Remove from Location</button>
                    @endcan
                
                &nbsp;
                <form action="{{ action([\Modules\Products\Http\Controllers\ProductController::class, 'massDeactivate']) }}" method="post" id="mass_deactivate_form">
                    @csrf
                    <input type="hidden" name="selected_products" id="selected_products">
                    <button type="submit" class="btn btn-xs btn-warning" id="deactivate-selected">Deactivate Selected</button>
                </form>
                &nbsp;
                @if($is_woocommerce)
                    <button type="button" class="btn btn-xs btn-warning toggle_woocomerce_sync">
                        Woocommerce Sync
                    </button>
                @endif
                </div>
            </td>
        </tr>
        <tr>
            <th><input type="checkbox" id="select-all-row" data-table-id="product_table"></th>
            <th>&nbsp;</th>
            <th>Action</th>
            <th>Product</th>
            <th>Business Location</th>
            @can('view_purchase_price')
                @php 
                    $colspan++;
                @endphp
                <th>Unit Purchase Price</th>
            @endcan
            @can('access_default_selling_price')
                @php 
                    $colspan++;
                @endphp
                <th>Selling Price</th>
            @endcan
            <th>Current Stock</th>
            <th>Product Type</th>
            <th>Category</th>
            <th>Brand</th>
            <th>Tax</th>
            <th>SKU</th>
            <th>Semi Finished</th>
            <th id="cf_1">{{ $custom_labels['product']['custom_field_1'] ?? '' }}</th>
            <th id="cf_2">{{ $custom_labels['product']['custom_field_2'] ?? '' }}</th>
            <th id="cf_3">{{ $custom_labels['product']['custom_field_3'] ?? '' }}</th>
            <th id="cf_4">{{ $custom_labels['product']['custom_field_4'] ?? '' }}</th>
        </tr>
    </thead>
    <tfoot>
        
    </tfoot>
</table>
