<div class="modal fade" id="paymentModal" tabindex="-1" role="dialog" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="paymentModalLabel">{{ __('Finalize Payment') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body bg-light">
                <form id="pos-sale-form">
                    @csrf
                    <div class="row">
                        {{-- Payment Info --}}
                        <div class="col-md-8">
                            <div class="card shadow-sm mb-3">
                                <div class="card-body">
                                    <h6 class="card-title mb-3 fw-bold">{{ __('Payment Details') }}</h6>
                                    
                                    <div class="payment-row mb-3 p-3 border rounded bg-white">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">{{ __('Amount') }}</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">$</span>
                                                    <input type="number" name="payment[0][amount]" id="payment-amount-0" class="form-control payment_amount" step="0.01">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">{{ __('Payment Method') }}</label>
                                                <select name="payment[0][method]" class="form-select payment_method">
                                                    @foreach($payment_types as $val => $label)
                                                        <option value="{{ $val }}">{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-12 account-field">
                                                <label class="form-label">{{ __('Payment Account') }}</label>
                                                <select name="payment[0][account_id]" class="form-select select2">
                                                    <option value="">{{ __('Select Account') }}</option>
                                                    @foreach($accounts as $id => $name)
                                                        <option value="{{ $id }}">{{ $name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-12">
                                                <label class="form-label">{{ __('Payment Note') }}</label>
                                                <textarea name="payment[0][note]" class="form-control" rows="1" placeholder="{{ __('Payment notes...') }}"></textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">{{ __('Sales Date') }}</label>
                                            <input type="datetime-local" name="transaction_date" class="form-control" value="{{ \Carbon\Carbon::now()->format('Y-m-d\TH:i') }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">{{ __('Invoice No') }}</label>
                                            <input type="text" name="invoice_no" class="form-control" placeholder="{{ __('Auto Generated') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Final Summary --}}
                        <div class="col-md-4">
                            <div class="card bg-dark text-white shadow-sm mb-3">
                                <div class="card-body">
                                    <h6 class="card-title text-info mb-4 fw-bold">{{ __('TOTAL PAYABLE') }}</h6>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>{{ __('Total') }}:</span>
                                        <input type="text" id="payment-total-payable" class="form-control-plaintext text-white text-end fw-bold" readonly value="0.00">
                                    </div>
                                    <hr class="bg-secondary">
                                    <div class="d-flex justify-content-between mb-2 text-warning">
                                        <span>{{ __('Total Paid') }}:</span>
                                        <span id="payment-total-paid" class="fw-bold">0.00</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-4 text-danger">
                                        <span>{{ __('Balance Due') }}:</span>
                                        <span id="payment-balance-due" class="fw-bold">0.00</span>
                                    </div>

                                    <button type="button" class="btn btn-info w-100 mb-2 font-weight-bold" id="complete-sale-btn">
                                        <i class="fa fa-check-circle"></i> {{ __('COMPLETE SALE') }}
                                    </button>
                                    <button type="button" class="btn btn-outline-light w-100" data-bs-dismiss="modal">
                                        {{ __('Back to Cart') }}
                                    </button>
                                </div>
                            </div>

                            <div class="alert alert-info py-2" style="font-size: 0.8rem;">
                                <i class="fa fa-info-circle"></i> {{ __('Quick Payment Methods are coming soon.') }}
                            </div>
                        </div>
                    </div>
                    
                    {{-- Hidden Fields for Cart Data --}}
                    <input type="hidden" name="status" value="final" id="sale_status">
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .payment-row {
        transition: border-color 0.3s;
    }
    .payment-row:focus-within {
        border-color: #28a745 !important;
    }
</style>

<script>
    $(document).ready(function() {
        $(document).on('change input', '.payment_amount', function() {
            let totalPayable = parseFloat($('#payment-total-payable').val()) || 0;
            let totalPaid = 0;
            $('.payment_amount').each(function() {
                totalPaid += parseFloat($(this).val()) || 0;
            });

            $('#payment-total-paid').text(totalPaid.toFixed(2));
            let balance = totalPayable - totalPaid;
            $('#payment-balance-due').text(balance.toFixed(2));
        });
    });
</script>
