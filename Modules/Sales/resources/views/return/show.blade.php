<div class="modal-dialog modal-xl" role="document">
  <div class="modal-content">
    <div class="modal-header border-bottom">
      <h4 class="modal-title" id="modalTitle"> @lang('Sales Return Details') (<b>@lang('Invoice No'):</b> #{{ $sale->invoice_no }})</h4>
      <button type="button" class="btn-close no-print" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>

    <div class="modal-body">
      <div class="row">
        @if(!empty($sale->return_parent))
        <div class="col-sm-6">
            <h4>@lang('Sales Return Details'):</h4>
            <strong>@lang('Return Date'):</strong> {{ \Carbon\Carbon::parse($sale->transaction_date)->format('d-m-Y') }}<br>
            <strong>@lang('Customer'):</strong> {{ $sale->contact->name }} <br>
            <strong>@lang('Location'):</strong> {{ $sale->location->name }}
        </div>
        <div class="col-sm-6">
            <h4>@lang('Sale Details'):</h4>
            <strong>@lang('Invoice No'):</strong> {{ $sale->return_parent->invoice_no }} <br>
            <strong>@lang('Date'):</strong> {{ \Carbon\Carbon::parse($sale->return_parent->transaction_date)->format('d-m-Y') }}
        </div>
        @else
            <div class="col-sm-6">
                <h4>@lang('Sales Return Details'):</h4>
                <strong>@lang('Return Date'):</strong> {{ \Carbon\Carbon::parse($sale->transaction_date)->format('d-m-Y') }}<br>
                <strong>@lang('Customer'):</strong> {{ $sale->contact->name ?? '' }} <br>
                <strong>@lang('Location'):</strong> {{ $sale->location->name }}
            </div>
        @endif
      </div>
      <br>
      <div class="row">
        <div class="col-sm-12">
          <table class="table table-bordered bg-gray">
            <thead>
              <tr class="bg-light">
                  <th>#</th>
                  <th>@lang('Product Name')</th>
                  <th>@lang('Unit Price')</th>
                  <th>@lang('Return Quantity')</th>
                  <th>@lang('Return Subtotal')</th>
              </tr>
          </thead>
          <tbody>
              @php
                $total_before_tax = 0;
              @endphp
              @foreach($sale->sell_lines as $sell_line)
              @if($sell_line->quantity_returned == 0)
                @continue
              @endif

              @php
                $unit_name = $sell_line->product->unit->short_name ?? '';
                if(!empty($sell_line->sub_unit)) {
                  $unit_name = $sell_line->sub_unit->short_name;
                }
              @endphp
              <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td>
                    {{ $sell_line->product->name }}
                    @if(!empty($sell_line->variations) && $sell_line->product->type == 'variable')
                      - {{ $sell_line->variations->product_variation->name ?? ''}}
                      - {{ $sell_line->variations->name ?? ''}}
                    @endif
                  </td>
                  <td><span class="display_currency" data-currency_symbol="true">{{ $sell_line->unit_price_inc_tax }}</span></td>
                  <td>{{@format_quantity($sell_line->quantity_returned)}} {{$unit_name}}</td>
                  <td>
                    @php
                      $line_total = $sell_line->unit_price_inc_tax * $sell_line->quantity_returned;
                      $total_before_tax += $line_total ;
                    @endphp
                    <span class="display_currency" data-currency_symbol="true">{{$line_total}}</span>
                  </td>
              </tr>
              @endforeach
            </tbody>
        </table>
      </div>
    </div>
    <div class="row">
      <div class="col-sm-6 offset-sm-6">
        <table class="table">
          <tr>
            <th>@lang('Net Total Amount'): </th>
            <td><span class="display_currency float-end" data-currency_symbol="true">{{ $total_before_tax }}</span></td>
          </tr>
          
          <tr>
            <th>@lang('Total Return Tax'):</th>
            <td class="text-end">
                @if(!empty($sale_taxes))
                  @foreach($sale_taxes as $k => $v)
                    <strong><small>{{$k}}</small></strong> - <span class="display_currency float-end" data-currency_symbol="true">{{ $v }}</span><br>
                  @endforeach
                @else
                0.00
                @endif
              </td>
          </tr>
          <tr>
            <th>@lang('Return Total'):</th>
            <td><span class="display_currency float-end" data-currency_symbol="true" >{{ $sale->final_total }}</span></td>
          </tr>
        </table>
      </div>
    </div>
    </div>

    <div class="modal-footer border-top">
      <button type="button" class="btn btn-primary no-print" aria-label="Print" 
      onclick="$(this).closest('div.modal-content').printThis();"><i class="fa fa-print"></i> @lang( 'Print' )
      </button>
      <button type="button" class="btn btn-secondary no-print" data-bs-dismiss="modal">@lang( 'Close' )</button>
    </div>
  </div>
</div>

<script type="text/javascript">
    function init_return_show() {
        if (typeof $ === 'undefined' || typeof __currency_convert_recursively === 'undefined') {
            setTimeout(init_return_show, 100);
            return;
        }
        var element = $('div.modal-xl');
        __currency_convert_recursively(element);
    }
    init_return_show();
</script>
