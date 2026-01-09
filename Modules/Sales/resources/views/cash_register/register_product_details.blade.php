<div class="row">
  <div class="col-md-12">
    <hr>
    <h3>{{ __('Product Sold Details') }}</h3>
    <table class="table table-bordered table-striped">
      <thead>
        <tr>
          <th>#</th>
          <th>{{ __('Product') }}</th>
          <th>{{ __('Brand') }}</th>
          <th>{{ __('Quantity') }}</th>
          <th>{{ __('Total Amount') }}</th>
        </tr>
      </thead>
      <tbody>
        @php
          $total_amount = 0;
          $total_quantity = 0;
        @endphp
        @foreach($details['product_details'] as $detail)
          <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $detail->product_name }}</td>
            <td>{{ $detail->brand_name ?? __('No Brand') }}</td>
            <td>
              {{ number_format($detail->total_quantity, 2) }}
              @php $total_quantity += $detail->total_quantity; @endphp
            </td>
            <td>
              {{ number_format($detail->total_amount, 2) }}
              @php $total_amount += $detail->total_amount; @endphp
            </td>
          </tr>
        @endforeach
      </tbody>
      <tfoot>
        <tr class="table-success">
          <th colspan="3">{{ __('Total') }}</th>
          <th>{{ number_format($total_quantity, 2) }}</th>
          <th>{{ number_format($total_amount, 2) }}</th>
        </tr>
      </tfoot>
    </table>
  </div>
</div>
