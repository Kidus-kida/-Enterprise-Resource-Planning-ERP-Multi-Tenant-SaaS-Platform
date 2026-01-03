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
                                    
                                    <div id="payment_rows_container">
                                        @if(!empty($transaction))
                                            @foreach($transaction->payment_lines as $payment_line)
                                                @include('sales::pos.partials.payment_row', ['row_index' => $loop->index, 'payment_line' => $payment_line->toArray(), 'removable' => $loop->index > 0])
                                            @endforeach
                                        @else
                                            @include('sales::pos.partials.payment_row', ['row_index' => 0, 'removable' => false])
                                        @endif
                                    </div>
                                    <div class="text-center mb-3">
                                        <button type="button" class="btn btn-outline-primary btn-sm" id="add_payment_row_btn">
                                            <i class="fa fa-plus"></i> {{ __('Add Payment Row') }}
                                        </button>
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


