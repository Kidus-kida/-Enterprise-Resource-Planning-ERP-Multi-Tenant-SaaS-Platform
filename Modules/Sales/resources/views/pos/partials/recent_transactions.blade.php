@if(!empty($transactions))
	<table class="table table-slim no-border">
		@foreach ($transactions as $transaction)
			<tr class="cursor-pointer" 
	    		data-toggle="tooltip"
	    		data-html="true"
	    		title="Customer: {{optional($transaction->contact)->name}} 
		    		@if(!empty($transaction->contact->mobile) && $transaction->contact->is_default == 0)
		    			<br/>Mobile: {{$transaction->contact->mobile}}
		    		@endif
	    		" >
				<td>
					{{ $loop->iteration}}.
				</td>
				<td>
					{{ $transaction->invoice_no }} ({{optional($transaction->contact)->name}})
				</td>
				<td class="display_currency">
					{{ $transaction->final_total }}
				</td>
				<td>
					<a href="{{route('sales.pos.edit', [$transaction->id])}}">
	    				<i class="fa fa-pencil text-muted" aria-hidden="true" title="{{__(' click_to_edit')}}"></i>
	    			</a>
	    			
	    			<a href="{{route('sales.pos.print_invoice', [$transaction->id])}}" class="print-invoice-link">
	    				<i class="fa fa-print text-muted" aria-hidden="true" title="{{__(' click_to_print')}}"></i>
	    			</a>
				</td>
			</tr>
		@endforeach
	</table>
@else
	<p>@lang('sale.no_recent_transactions')</p>
@endif
