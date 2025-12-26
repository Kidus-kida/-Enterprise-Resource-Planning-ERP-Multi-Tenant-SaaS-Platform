<div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">@lang('lang_v1.view_shipping')</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
            <div class="row mb-4">
                <div class="col-md-4">
                    <label class="form-label fw-bold">@lang('lang_v1.shipment_status'):</label>
                    <div class="mt-1">
                        @if($transfer_shipment->shipment_status == 'received')
                            <span class="badge bg-warning text-dark">Received</span>
                        @elseif($transfer_shipment->shipment_status == 'delivered')
                            <span class="badge bg-success">Delivered</span>
                        @elseif($transfer_shipment->shipment_status == 'inprogress')
                            <span class="badge bg-info">In Progress</span>
                        @else
                            <span class="badge bg-secondary">{{ $transfer_shipment->shipment_status }}</span>
                        @endif
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">@lang('lang_v1.driver'):</label>
                    <p>{{ $transfer_shipment->driver_name }}</p>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">@lang('lang_v1.plate_number'):</label>
                    <p>{{ $transfer_shipment->plate }}</p>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">@lang('lang_v1.vehicle_brand'):</label>
                    <p>{{ $transfer_shipment->car_brand }}</p>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">@lang('lang_v1.vehicle_model'):</label>
                    <p>{{ $transfer_shipment->car_model }}</p>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">@lang('lang_v1.assigned_date'):</label>
                    <p>{{ $transfer_shipment->assigned_date }}</p>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">@lang('lang_v1.delivered_date'):</label>
                    <p>{{ $transfer_shipment->delivered_date ?? 'Not yet delivered' }}</p>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">@lang('lang_v1.created_by'):</label>
                    <p>{{ $transfer_shipment->created_by }}</p>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-12">
                    <h5 class="text-primary mb-3">@lang('lang_v1.shipping_requests')</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Requested qty</th>
                                    <th>Request Date</th>
                                    <th>Approved qty</th>
                                    <th>From store</th>
                                    <th>To Store</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($requests as $str)
                                    <tr>
                                        <td>{{ $str->product_name }}</td>
                                        <td>{{ $str->qty }}</td>
                                        <td>{{ $str->created_at }}</td>
                                        <td>{{ $str->approved_qty }}</td>
                                        <td>{{ $str->from_store }}</td>
                                        <td>{{ $str->to_store }}</td>
                                        <td>
                                            @if($str->status == 'requested')
                                                <span class="text-danger">Requested</span>
                                            @elseif($str->status == 'issued')
                                                <span class="text-primary">Approved</span>
                                            @elseif($str->status == 'received')
                                                <span class="text-success">Received</span>
                                            @else
                                                {{ $str->status }}
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('messages.close')</button>
        </div>
    </div>
</div>