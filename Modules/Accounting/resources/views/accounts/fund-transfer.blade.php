<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content border-0 shadow-lg rounded-4">
        <div class="modal-header bg-white border-bottom-0 pb-0">
            <h5 class="modal-title fw-bold text-dark d-flex align-items-center">
                <span class="bg-primary bg-opacity-10 text-primary rounded-circle p-2 me-2">
                    <i class="fa fa-exchange-alt"></i>
                </span>
                {{ __('accounting::lang.fund_transfer') }}
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <form action="{{ action([\Modules\Accounting\Http\Controllers\AccountController::class, 'postFundTransfer']) }}"
            method="post" enctype="multipart/form-data">
            @csrf
            <div class="modal-body p-4">
                <div class="row g-3">
                    <!-- From Account (Readonly if set, or Select) -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">{{ __('accounting::lang.from') }}</label>
                        <input type="text" class="form-control bg-light border-0" value="{{ $account->name }}"
                            readonly>
                        <input type="hidden" name="from_account" value="{{ $account->id }}">
                        <div class="form-text text-muted">{{ __('Current Balance') }}: @format_currency($account->balance)</div>
                    </div>

                    <!-- To Account -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">{{ __('accounting::lang.to') }} <span
                                class="text-danger">*</span></label>
                        <select class="form-select bg-light border-0" name="to_account" required>
                            <option value="">{{ __('messages.please_select') }}</option>
                            @foreach ($accounts as $id => $name)
                                @if ($id != $account->id)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <!-- Amount -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">{{ __('accounting::lang.amount') }} <span
                                class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0">Br</span>
                            <input type="number" name="amount" class="form-control bg-light border-0" step="0.01"
                                required placeholder="0.00">
                        </div>
                    </div>

                    <!-- Date -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">{{ __('messages.date') }}</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0"><i class="fa fa-calendar"></i></span>
                            <input type="text" name="operation_date"
                                class="form-control bg-light border-0 datepicker" value="{{ @format_date('now') }}"
                                required>
                        </div>
                    </div>

                    <!-- Note -->
                    <div class="col-md-12">
                        <label class="form-label fw-semibold">{{ __('accounting::lang.note') }}</label>
                        <textarea name="note" class="form-control bg-light border-0" rows="3"
                            placeholder="{{ __('Fund Transfer Note') }}"></textarea>
                    </div>

                    <!-- Attachment -->
                    <div class="col-md-12">
                        <label class="form-label fw-semibold">{{ __('lang_v1.upload_documents') }}</label>
                        <input type="file" name="attachment" class="form-control bg-light border-0">
                        <div class="form-text text-muted">{{ __('Max File size: 5MB') }}</div>
                    </div>

                </div>
            </div>
            <div class="modal-footer border-top-0 pt-0 pb-4 pe-4">
                <button type="button" class="btn btn-light rounded-pill px-4"
                    data-bs-dismiss="modal">{{ __('messages.close') }}</button>
                <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                    <i class="fa fa-exchange-alt me-1"></i> {{ __('accounting::lang.transfer') }}
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    $('.datepicker').datepicker({
        autoclose: true,
        format: datepicker_date_format
    });
</script>
