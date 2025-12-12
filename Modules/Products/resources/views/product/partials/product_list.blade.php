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
                        <button type="submit" class="btn btn-xs btn-danger" id="delete-selected">{{__('lang_v1.delete_selected')}}</button>
                    </form>
                @endcan

                
                    @can('product.update')
                    
                        @if(config('constants.enable_product_bulk_edit'))
                            &nbsp;
                            <form action="{{ action([\Modules\Products\Http\Controllers\ProductController::class, 'bulkEdit']) }}" method="post" id="bulk_edit_form">
                                @csrf
                                <input type="hidden" name="selected_products" id="selected_products_for_edit">
                                <button type="submit" class="btn btn-xs btn-primary" id="edit-selected"> <i class="fa fa-edit"></i>{{__('lang_v1.bulk_edit')}}</button>
                            </form>
                        @endif
                        &nbsp;
                        <button type="button" class="btn btn-xs btn-success update_product_location" data-type="add">@lang('lang_v1.add_to_location')</button>
                        &nbsp;
                        <button type="button" class="btn btn-xs bg-navy update_product_location" data-type="remove">@lang('lang_v1.remove_from_location')</button>
                    @endcan
                
                &nbsp;
                <form action="{{ action([\Modules\Products\Http\Controllers\ProductController::class, 'massDeactivate']) }}" method="post" id="mass_deactivate_form">
                    @csrf
                    <input type="hidden" name="selected_products" id="selected_products">
                    <button type="submit" class="btn btn-xs btn-warning" id="deactivate-selected">{{__('lang_v1.deactivate_selected')}}</button>
                </form> @show_tooltip(__('lang_v1.deactive_product_tooltip'))
                &nbsp;
                @if($is_woocommerce)
                    <button type="button" class="btn btn-xs btn-warning toggle_woocomerce_sync">
                        @lang('lang_v1.woocommerce_sync')
                    </button>
                @endif
                </div>
            </td>
        </tr>
        <tr>
            <th><input type="checkbox" id="select-all-row" data-table-id="product_table"></th>
            <th>&nbsp;</th>
            <th>@lang('messages.action')</th>
            <th>@lang('sale.product')</th>
            <th>@lang('purchase.business_location') @show_tooltip(__('lang_v1.product_business_location_tooltip'))</th>
            @can('view_purchase_price')
                @php 
                    $colspan++;
                @endphp
                <th>@lang('lang_v1.unit_perchase_price')</th>
            @endcan
            @can('access_default_selling_price')
                @php 
                    $colspan++;
                @endphp
                <th>@lang('lang_v1.selling_price')</th>
            @endcan
            <th>@lang('report.current_stock')</th>
            <th>@lang('product.product_type')</th>
            <th>@lang('product.category')</th>
            <th>@lang('product.brand')</th>
            <th>@lang('product.tax')</th>
            <th>@lang('product.sku')</th>
            <th>@lang('unit.semi_finished')</th>
            <th id="cf_1">{{ $custom_labels['product']['custom_field_1'] ?? '' }}</th>
            <th id="cf_2">{{ $custom_labels['product']['custom_field_2'] ?? '' }}</th>
            <th id="cf_3">{{ $custom_labels['product']['custom_field_3'] ?? '' }}</th>
            <th id="cf_4">{{ $custom_labels['product']['custom_field_4'] ?? '' }}</th>
        </tr>
    </thead>
    <tfoot>
        
    </tfoot>
</table>
