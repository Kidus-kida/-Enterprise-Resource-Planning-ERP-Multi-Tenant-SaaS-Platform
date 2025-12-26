<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <form action="{{ route('stock-transfers-request.updateShipping', $shipping->id) }}" method="POST"
            id="edit_transfer_shipment_form">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h4 class="modal-title">@lang('lang_v1.edit_shipping')</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label" for="edit_driver_id">@lang('lang_v1.driver'):</label>
                            <select class="form-control select2" name="driver_id" id="edit_driver_id"
                                style="width: 100%;" required>
                                <option value="">@lang('messages.please_select')</option>
                                @foreach($driver as $id => $name)
                                    <option value="{{ $id }}" @if($shipping->driver_id == $id) selected @endif>{{ $name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label" for="edit_request_id">@lang('lang_v1.request_id'):</label>
                            @php
                                $selected_ids = explode(',', $shipping->request_id);
                            @endphp
                            <select class="form-control select2" name="request_id[]" id="edit_request_id"
                                style="width: 100%;" multiple required>
                                @foreach($items as $id => $name)
                                    <option value="{{ $id }}" @if(in_array($id, $selected_ids)) selected @endif>{{ $name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label"
                                for="edit_shipment_status">@lang('lang_v1.shipment_status'):</label>
                            <select class="form-control" name="shipment_status" id="edit_shipment_status" required>
                                <option value="">@lang('messages.please_select')</option>
                                <option value="received" @if($shipping->shipment_status == 'received') selected @endif>
                                    @lang('lang_v1.received')
                                </option>
                                <option value="inprogress" @if($shipping->shipment_status == 'inprogress') selected
                                @endif>@lang('lang_v1.inprogress')</option>
                                <option value="delivered" @if($shipping->shipment_status == 'delivered') selected @endif>
                                    @lang('lang_v1.delivered')
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label" for="edit_assigned_date">@lang('lang_v1.assigned_date'):</label>
                            <input type="datetime-local" name="assigned_date" id="edit_assigned_date"
                                class="form-control"
                                value="{{ \Carbon\Carbon::parse($shipping->assigned_date)->format('Y-m-d\TH:i') }}"
                                required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('messages.close')</button>
            </div>
        </form>
    </div>
</div>

<script>
    $(document).ready(function () {
        $('#edit_transfer_shipment_form .select2').select2({
            dropdownParent: $('#edit_transfer_shipment_form')
        });
    });
</script>