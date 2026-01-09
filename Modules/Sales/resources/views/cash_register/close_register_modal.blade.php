<div class="modal-dialog modal-lg" role="document">
  <div class="modal-content">
    <form action="{{ route('sales.cash-register.post-close') }}" method="POST">
      @csrf
      <div class="modal-header">
        <h5 class="modal-title">{{ __('Close Register') }} ({{ \Carbon\Carbon::parse($register_details->open_time)->format('jS M, Y h:i A') }} - {{ \Carbon\Carbon::now()->format('jS M, Y h:i A') }})</h5>
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
              <tr class="table-info">
                <th>{{ __('Total Cash') }}:</th>
                <th class="text-end">{{ number_format($register_details->cash_in_hand + $register_details->total_cash - $register_details->total_cash_refund, 2) }}</th>
              </tr>
            </table>
          </div>
        </div>

        <div class="row mt-3">
          <div class="col-sm-4">
            <div class="mb-3">
              <label for="closing_amount" class="form-label fw-bold">{{ __('Total Cash') }}:*</label>
              <input type="number" name="closing_amount" id="closing_amount" class="form-control" value="{{ number_format($register_details->cash_in_hand + $register_details->total_cash - $register_details->total_cash_refund, 2, '.', '') }}" step="0.01" required>
            </div>
          </div>
          <div class="col-sm-4">
            <div class="mb-3">
              <label for="total_card_slips" class="form-label fw-bold">{{ __('Total Card Slips') }}:*</label>
              <input type="number" name="total_card_slips" id="total_card_slips" class="form-control" value="{{ $register_details->total_card_slips }}" min="0" required>
            </div>
          </div>
          <div class="col-sm-4">
            <div class="mb-3">
              <label for="total_cheques" class="form-label fw-bold">{{ __('Total Cheques') }}:*</label>
              <input type="number" name="total_cheques" id="total_cheques" class="form-control" value="{{ $register_details->total_cheques }}" min="0" required>
            </div>
          </div>
          <div class="col-sm-12">
            <div class="mb-3">
              <label for="closing_note" class="form-label fw-bold">{{ __('Closing Note') }}:</label>
              <textarea name="closing_note" id="closing_note" class="form-control" rows="3"></textarea>
            </div>
          </div>
        </div>

        @include('sales::cash_register.register_product_details')
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
        <button type="submit" class="btn btn-danger">{{ __('Close Register') }}</button>
      </div>
    </form>
  </div>
</div>
