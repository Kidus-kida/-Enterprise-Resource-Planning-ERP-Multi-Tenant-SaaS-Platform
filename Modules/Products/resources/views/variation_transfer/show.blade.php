<div class="modal-header">
    <h5 class="modal-title">@lang('lang_v1.variation_transfer_details') ( @lang('lang_v1.date'): {{
        @format_date($transfer->date) }} )</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

<div class="modal-body">
    <div class="row">
        <div class="col-sm-6">
            <p><strong>@lang('lang_v1.from_location'):</strong> {{ $transfer->location_from->name ?? '' }}</p>
            <p><strong>@lang('lang_v1.from_store'):</strong> {{ $transfer->store_from->name ?? '' }}</p>
            <p><strong>@lang('lang_v1.product_from'):</strong> {{ $transfer->variation_from->full_name ?? '' }}</p>
        </div>
        <div class="col-sm-6">
            <p><strong>@lang('lang_v1.to_location'):</strong> {{ $transfer->location_to->name ?? '' }}</p>
            <p><strong>@lang('lang_v1.to_store'):</strong> {{ $transfer->store_to->name ?? '' }}</p>
            <p><strong>@lang('lang_v1.product_to'):</strong> {{ $transfer->variation_to->full_name ?? '' }}</p>
        </div>
    </div>
    <hr>
    <div class="row">
        <div class="col-sm-4">
            <p><strong>@lang('lang_v1.qty'):</strong> {{ @num_format($transfer->qty) }}</p>
        </div>
        <div class="col-sm-4">
            <p><strong>@lang('lang_v1.unit_cost'):</strong> {{ @num_format($transfer->unit_cost) }}</p>
        </div>
        <div class="col-sm-4">
            <p><strong>@lang('lang_v1.total_cost'):</strong> {{ @num_format($transfer->total_cost) }}</p>
        </div>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('messages.close')</button>
</div>