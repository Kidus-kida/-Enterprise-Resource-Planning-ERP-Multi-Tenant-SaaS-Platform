<div class="modal-dialog modal-lg" role="document">
  <div class="modal-content">
    <div class="modal-header">
      <h5 class="modal-title">{{ __('Register Details') }} ({{ \Carbon\Carbon::parse($register_details->open_time)->format('jS M, Y h:i A') }})</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>

    <div class="modal-body">
      <div class="row">
        <div class="col-sm-12">
          <table class="table table-sm">
            <tr>
              <td>{{ __('Cash in hand') }}:</td>
              <td class="text-end">{{ number_format($register_details->cash_in_hand, 2) }}</td>
            </tr>
            <tr>
              <td>{{ __('Cash Payment') }}:</td>
              <td class="text-end">{{ number_format($register_details->total_cash, 2) }}</td>
            </tr>
            <tr>
              <td>{{ __('Cheque Payment') }}:</td>
              <td class="text-end">{{ number_format($register_details->total_cheque, 2) }}</td>
            </tr>
            <tr>
              <td>{{ __('Card Payment') }}:</td>
              <td class="text-end">{{ number_format($register_details->total_card, 2) }}</td>
            </tr>
            <tr>
              <td>{{ __('Bank Transfer') }}:</td>
              <td class="text-end">{{ number_format($register_details->total_bank_transfer, 2) }}</td>
            </tr>
            <tr class="table-info">
              <th>{{ __('Total Sales') }}:</th>
              <th class="text-end">{{ number_format($details['transaction_details']->total_sales, 2) }}</th>
            </tr>
          </table>
        </div>
      </div>
    
      @include('sales::cash_register.register_product_details')

      <div class="row mt-4">
        <div class="col-sm-6">
          <p><strong>{{ __('User') }}:</strong> {{ $register_details->user_name }}</p>
          <p><strong>{{ __('Location') }}:</strong> {{ $register_details->location_name }}</p>
        </div>
      </div>
    </div>

    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
      <button type="button" class="btn btn-primary" onclick="window.print();">{{ __('Print') }}</button>
    </div>
  </div>
</div>
