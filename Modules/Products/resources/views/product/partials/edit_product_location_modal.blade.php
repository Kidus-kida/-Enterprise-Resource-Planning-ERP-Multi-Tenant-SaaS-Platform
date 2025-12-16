<div class="modal fade" id="edit_product_location_modal" tabindex="-1" role="dialog" 
    aria-labelledby="gridSystemModalLabel">
    <div class="modal-dialog" role="document">
	    <div class="modal-content">
	    	<form action="{{ action([\Modules\Products\Http\Controllers\ProductController::class, 'updateProductLocation']) }}" method="post" id="edit_product_location_form">
                @csrf
		    	<div class="modal-header">
			    	<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				      <h4 class="modal-title"><span class="add_to_location_title hide">@lang( 'lang_v1.add_location_to_the_selected_products' )</span><span class="remove_from_location_title hide">@lang( 'lang_v1.remove_location_from_the_selected_products' )</span></h4>
			    </div>
			    <div class="modal-body">
			    	<div class="form-group">
                        <label for="product_location">@lang('purchase.business_location'):</label>
                        <select name="product_location[]" class="form-control" style="width:100%" required multiple id="product_location">
                            @foreach($business_locations as $key => $value)
                                <option value="{{ $key }}">{{ $value }}</option>
                            @endforeach
                        </select>
		                <input type="hidden" name="products" id="products_to_update_location">
		                <input type="hidden" name="update_type" id="update_type">
		            </div>
			    </div>
			    <div class="modal-footer">
		      		<button type="submit" class="btn btn-primary" id="update_product_location">@lang( 'messages.save' )</button>
		      		<button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
		    	</div>
	    	</form>
	    </div>
    </div>
</div>