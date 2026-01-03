<form action="{{ route('stock-transfers-request.addshipment') }}" method="POST" id="add_transfer_shipment_form">
    @csrf
    <div class="modal-body">
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label" for="driver_id">@lang('Driver'):</label>
                    <select class="form-control select2" name="driver_id" id="driver_id" style="width: 100%;" required>
                        <option value="">@lang('please select')</option>
                        @foreach($driver as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label" for="request_id">@lang('Select items'):</label>
                    <select class="form-control select2" name="request_id[]" id="request_id" style="width: 100%;"
                        multiple required>
                        @foreach($requests as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label" for="shipment_status">@lang('Shipping Status'):</label>
                    <select class="form-control" name="shipment_status" id="shipment_status" required>
                        <option value="">@lang('please select')</option>
                        <option value="received">@lang('received')</option>
                        <option value="inprogress">@lang('inprogress')</option>
                        <option value="delivered">@lang('delivered')</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label" for="assigned_date">@lang('Assigned Date'):</label>
                    <input type="datetime-local" name="assigned_date" id="assigned_date" class="form-control" required>
                </div>
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <button type="submit" class="btn btn-primary">@lang('Save')</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('Close')</button>
    </div>
</form>

<script>
    $(document).ready(function () {
        $('#add_transfer_shipment_form .select2').select2({
            dropdownParent: $('#add_transfer_shipment_form')
        });

        // Set default assigned date to now
        var now = new Date();
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
        $('#assigned_date').val(now.toISOString().slice(0, 16));
    });
</script>