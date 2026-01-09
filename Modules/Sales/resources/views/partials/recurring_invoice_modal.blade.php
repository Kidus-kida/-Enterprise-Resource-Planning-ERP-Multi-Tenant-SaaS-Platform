<div class="modal fade" tabindex="-1" role="dialog" id="recurringInvoiceModal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{ __('Subscribe') }}</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="recur_interval">{{ __('Interval') }}:*</label>
                            <div class="input-group">
                                <input type="number" name="recur_interval" id="recur_interval" class="form-control" style="width: 50%;" value="{{ $transaction->recur_interval ?? '' }}">
                                <select name="recur_interval_type" id="recur_interval_type" class="form-control" style="width: 50%;">
                                    <option value="days" {{ ($transaction->recur_interval_type ?? '') == 'days' ? 'selected' : '' }}>{{ __('Days') }}</option>
                                    <option value="months" {{ ($transaction->recur_interval_type ?? '') == 'months' ? 'selected' : '' }}>{{ __('Months') }}</option>
                                    <option value="years" {{ ($transaction->recur_interval_type ?? '') == 'years' ? 'selected' : '' }}>{{ __('Years') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="recur_repetitions">{{ __('No. of Repetitions') }}:</label>
                            <input type="number" name="recur_repetitions" id="recur_repetitions" class="form-control" value="{{ $transaction->recur_repetitions ?? '' }}">
                            <small class="text-muted">{{ __('If blank, it will be infinite') }}</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
            </div>
        </div>
    </div>
</div>
