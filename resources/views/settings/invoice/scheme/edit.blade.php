<div class="modal-dialog" role="document">
    <div class="modal-content">
        <form action="{{ action([\App\Http\Controllers\InvoiceSchemeController::class, 'update'], [$invoice->id]) }}"
            method="POST" id="invoice_scheme_edit_form">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title">Edit Invoice Scheme</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group mb-3">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required
                                value="{{ $invoice->name }}">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group mb-3">
                            <label class="form-label">Scheme Type <span class="text-danger">*</span></label>
                            <select name="scheme_type" class="form-control" required>
                                <option value="blank" {{ $invoice->scheme_type == 'blank' ? 'selected' : '' }}>Blank
                                </option>
                                <option value="year" {{ $invoice->scheme_type == 'year' ? 'selected' : '' }}>Year
                                    based (YYYY-)</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group mb-3">
                            <label class="form-label">Prefix</label>
                            <input type="text" name="prefix" class="form-control" value="{{ $invoice->prefix }}">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group mb-3">
                            <label class="form-label">Start Number</label>
                            <input type="number" name="start_number" class="form-control"
                                value="{{ $invoice->start_number }}">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group mb-3">
                            <label class="form-label">Total Digits</label>
                            <input type="number" name="total_digits" class="form-control"
                                value="{{ $invoice->total_digits }}">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_default" value="1"
                                id="is_default_edit" {{ $invoice->is_default ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_default_edit">
                                Set as default
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Update</button>
            </div>
        </form>
    </div>
</div>
