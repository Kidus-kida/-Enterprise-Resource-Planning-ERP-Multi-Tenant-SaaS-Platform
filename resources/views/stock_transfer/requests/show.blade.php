<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">@lang('lang_v1.view_request')</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
            <div class="row">
                <div class="col-md-4">
                    <label class="form-label">@lang('lang_v1.request_location'):</label>
                    <p>{{ $transfer_request->rl_name }}</p>
                </div>
                <div class="col-md-4">
                    <label class="form-label">@lang('lang_v1.request_to_location'):</label>
                    <p>{{ $transfer_request->rtl_name }}</p>
                </div>
                <div class="col-md-4">
                    <label class="form-label">@lang('lang_v1.rqstore'):</label>
                    <p>{{ $transfer_request->store_name }}</p>
                </div>
                <div class="col-md-4">
                    <label class="form-label">@lang('lang_v1.category'):</label>
                    <p>{{ $transfer_request->category_name }}</p>
                </div>
                <div class="col-md-4">
                    <label class="form-label">@lang('lang_v1.sub_category'):</label>
                    <p>{{ $transfer_request->sub_category_name }}</p>
                </div>
                <div class="col-md-4">
                    <label class="form-label">@lang('lang_v1.product'):</label>
                    <p>{{ $transfer_request->product_name }}</p>
                </div>
                <div class="col-md-4">
                    <label class="form-label">@lang('lang_v1.qty'):</label>
                    <p class="text-danger fw-bold">{{ $transfer_request->qty }}</p>
                </div>
                <div class="col-md-4">
                    <label class="form-label">@lang('lang_v1.approved_qty'):</label>
                    <p class="fw-bold" style="color: {{ is_null($transfer_request->approved_qty) ? 'red' : 'green' }};">
                        {{ is_null($transfer_request->approved_qty) ? 'Pending' : $transfer_request->approved_qty }}
                    </p>
                </div>
                <div class="col-md-4">
                    <label class="form-label">@lang('lang_v1.delivery_need_on'):</label>
                    <p class="text-danger">
                        {{ \Carbon\Carbon::parse($transfer_request->delivery_need_on)->format('d-m-Y') }}</p>
                </div>
                <div class="col-md-4">
                    <label class="form-label">@lang('lang_v1.status'):</label>
                    <p><span class="badge bg-info">{{ ucfirst($transfer_request->status) }}</span></p>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('messages.close')</button>
        </div>
    </div>
</div>