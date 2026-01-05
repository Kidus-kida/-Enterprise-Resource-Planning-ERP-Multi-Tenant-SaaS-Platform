<div class="modal-dialog" role="document">
    <div class="modal-content">
        <form action="{{ action([\App\Http\Controllers\InvoiceSchemeController::class, 'store']) }}" method="POST"
            id="invoice_scheme_add_form">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Add Invoice Scheme</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group mb-3">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required placeholder="Name">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group mb-3">
                            <label class="form-label">Scheme Type <span class="text-danger">*</span></label>
                            <select name="scheme_type" class="form-control" required>
                                <option value="blank">Blank</option>
                                <option value="year">Year based (YYYY-)</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group mb-3">
                            <label class="form-label">Prefix</label>
                            <input type="text" name="prefix" class="form-control" placeholder="Prefix">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group mb-3">
                            <label class="form-label">Start Number</label>
                            <input type="number" name="start_number" class="form-control" value="1">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group mb-3">
                            <label class="form-label">Total Digits</label>
                            <input type="number" name="total_digits" class="form-control" value="4">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_default" value="1"
                                id="is_default">
                            <label class="form-check-label" for="is_default">
                                Set as default
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>
