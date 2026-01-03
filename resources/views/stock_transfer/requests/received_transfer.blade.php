<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <form action="{{ route('stock-transfers-request.postReceivedTransfer', $rquest_transfer->id) }}" method="POST"
            id="received_transfer_form">
            @csrf
            <div class="modal-header">
                <h4 class="modal-title">Received Quantity Form</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="mb-3">
                    <h5 class="text-success">Approved Qty: {{ $rquest_transfer->approved_qty }}</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>@lang('lang_v1.product')</th>
                                <th>@lang('lang_v1.good_condition')</th>
                                <th>@lang('lang_v1.damage')</th>
                                <th>@lang('lang_v1.short')</th>
                                <th>@lang('lang_v1.expire')</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{{ $rquest_transfer->products->name }}</td>
                                <td><input type="number" name="good_condition" class="form-control" value="0" min="0">
                                </td>
                                <td><input type="number" name="damage" class="form-control" value="0" min="0"></td>
                                <td><input type="number" name="short" class="form-control" value="0" min="0"></td>
                                <td><input type="number" name="expire" class="form-control" value="0" min="0"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('messages.close')</button>
            </div>
        </form>
    </div>
</div>