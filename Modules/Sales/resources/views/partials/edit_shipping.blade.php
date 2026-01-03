<div class="modal-dialog" role="document">
	{!! Form::open(['url' => route('sales.update_shipping', [$transaction->id]), 'method' => 'put', 'id' => 'edit_shipping_form' ]) !!}
	<div class="modal-content">
		<div class="modal-header">
			<h4 class="modal-title">@lang(' edit_shipping') - {{$transaction->invoice_no}}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
		</div>
		<div class="modal-body">
			<div class="row">
				<div class="col-md-6">
			        <div class="form-group">
			            {!! Form::label('shipping_details', __('sale.shipping_details') . ':*' ) !!}
			            {!! Form::textarea('shipping_details', !empty($transaction->shipping_details) ? $transaction->shipping_details : '', ['class' => 'form-control','placeholder' => __('sale.shipping_details'), 'required' ,'rows' => '4']); !!}
			        </div>
			    </div>

			    <div class="col-md-6">
			        <div class="form-group">
			            {!! Form::label('shipping_address', __(' shipping_address') . ':' ) !!}
			            {!! Form::textarea('shipping_address',!empty($transaction->shipping_address) ? $transaction->shipping_address : '', ['class' => 'form-control','placeholder' => __(' shipping_address') ,'rows' => '4']); !!}
			        </div>
			    </div>

			    <div class="col-md-6">
			        <div class="form-group">
			            {!! Form::label('shipping_status', __(' shipping_status') . ':' ) !!}
			            {!! Form::select('shipping_status',$shipping_statuses, !empty($transaction->shipping_status) ? $transaction->shipping_status : null, ['class' => 'form-control','placeholder' => __('messages.please_select')]); !!}
			        </div>
			    </div>

			    <div class="col-md-6">
			        <div class="form-group">
			            {!! Form::label('delivered_to', __(' delivered_to') . ':' ) !!}
			            {!! Form::text('delivered_to', !empty($transaction->delivered_to) ? $transaction->delivered_to : null, ['class' => 'form-control','placeholder' => __(' delivered_to')]); !!}
			        </div>
			    </div>

			</div>
		</div>
		<div class="modal-footer">
			<button type="submit" class="btn btn-primary">@lang('messages.update')</button>
		    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('messages.cancel')</button>
		</div>
		{!! Form::close() !!}
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
