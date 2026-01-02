<div class="modal-dialog modal-xl" role="document">
	<div class="modal-content">
		<div class="modal-header">
		    <h4 class="modal-title" id="modalTitle"> Stock Adjustment Details (<b>Reference No:</b> #{{ $stock_adjustment->ref_no }})
		    </h4>
            <button type="button" class="btn-close no-print" data-bs-dismiss="modal" aria-label="Close"></button>
		</div>
		<div class="modal-body">
		  	<div class="row">
			    <div class="col-sm-12">
			      <p class="float-end"><b>Date:</b> {{ format_date($stock_adjustment->transaction_date) }}</p>
			    </div>
			</div>
			<div class="row invoice-info g-3">
				<div class="col-sm-4 invoice-col">
    				Business:
			     	 <address>
			        <strong>{{ $stock_adjustment->business->name }}</strong><br>
			        {{ $stock_adjustment->location->name }}
			        @if(!empty($stock_adjustment->location->landmark))
			          <br>{{$stock_adjustment->location->landmark}}
			        @endif
			        @if(!empty($stock_adjustment->location->city) || !empty($stock_adjustment->location->state) || !empty($stock_adjustment->location->country))
			          <br>{{implode(',', array_filter([$stock_adjustment->location->city, $stock_adjustment->location->state, $stock_adjustment->location->country]))}}
			        @endif
			        @if(!empty($stock_adjustment->location->mobile))
			          <br>Mobile: {{$stock_adjustment->location->mobile}}
			        @endif
			        @if(!empty($stock_adjustment->location->email))
			          <br>Email: {{$stock_adjustment->location->email}}
			        @endif
			      </address>
			    </div>

			    <div class="col-sm-4 invoice-col">
			      	<b>Reference No:</b> #{{ $stock_adjustment->ref_no }}<br/>
			      	<b>Date:</b> {{ format_date($stock_adjustment->transaction_date) }}<br/>
			      	<b>Adjustment Type:</b> {{ ucfirst($stock_adjustment->adjustment_type) }}<br>
			      	<b>Reason:</b> {{ $stock_adjustment->additional_notes }}<br>
			    </div>
    		</div>

    		<div class="row mt-4">
    			<div class="col-sm-12 col-xs-12">
      				<div class="table-responsive">
      					<table class="table table-condensed table-bordered">
							<thead class="bg-light">
                                <tr>
                                    <th>Product</th>
                                    @if(!empty($lot_n_exp_enabled))
                                        <th>Lot & Expiry</th>
                                    @endif
                                    <th>Quantity</th>
                                    <th>Unit Price</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach( $stock_adjustment->stock_adjustment_lines as $stock_adjustment_line )
                                    <tr>
                                        <td>
                                            {{ $stock_adjustment_line->variation->full_name ?? ($stock_adjustment_line->variation->product->name . ' ' . $stock_adjustment_line->variation->name) }}
                                        </td>
                                        @if(!empty($lot_n_exp_enabled))
                                            <td>{{ $stock_adjustment_line->lot_details->lot_number ?? '--' }}
                                                @if( session()->get('business.enable_product_expiry') == 1 && !empty($stock_adjustment_line->lot_details->exp_date))
                                                ({{ format_date($stock_adjustment_line->lot_details->exp_date) }})
                                                @endif
                                            </td>
                                        @endif
                                        <td>
                                            {{ format_quantity($stock_adjustment_line->quantity) }}
                                        </td>
                                        <td>
                                            {{ num_format($stock_adjustment_line->unit_price) }}
                                        </td>
                                        <td>
                                            {{ num_format($stock_adjustment_line->unit_price * $stock_adjustment_line->quantity) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
						</table>
      				</div>
     			</div>
     			<div class="col-md-6 offset-md-6 col-sm-12">
				    <div class="table-responsive">
				        <table class="table table-borderless">
				          	<tr>
				            	<th class="text-end">Total Amount: </th>
				            	<td class="text-end"><span>{{ num_format($stock_adjustment->final_total) }}</span></td>
				          	</tr>
				          	<tr>
				            	<th class="text-end">Total Amount Recovered: </th>
				            	<td class="text-end"><span>{{ num_format($stock_adjustment->total_amount_recovered) }}</span></td>
				          	</tr>
				      	</table>
				  	</div>
				</div>
    		</div>
		</div>
		<div class="modal-footer">
			<button type="button" class="btn btn-primary no-print" aria-label="Print" 
			onclick="window.print();"><i class="fa fa-print"></i> Print
			</button>
			<button type="button" class="btn btn-secondary no-print" data-bs-dismiss="modal">Close</button>
		</div>
	</div>
</div>
