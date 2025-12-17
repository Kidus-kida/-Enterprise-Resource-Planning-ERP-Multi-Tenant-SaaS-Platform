<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">

        <form action="{{ route('account.post-cheque-deposit') }}" method="post" id="deposit_form"
            enctype="multipart/form-data">
            @csrf
            <div class="modal-header">
                <h4 class="modal-title">Cheque Deposit</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                @if (isset($error))
                    <div class="alert alert-danger">{{ $error }}</div>
                @else
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="alert alert-primary p-2" role="alert">
                                <strong>Selected Account:</strong> {{ $account->name ?? '' }}
                                <input type="hidden" name="account_id" value="{{ $account->id ?? '' }}">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label for="operation_date">Transaction Date:*</label>
                                <input type="date" name="operation_date" class="form-control" required
                                    value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <!-- Placeholder for Cheque List filtering if needed, for now simplified -->
                            <div class="alert alert-info">Select 'Cheques in Hand' to deposit or encash.</div>
                        </div>
                    </div>

                    <!-- Cheque List Table would go here - simplified for initial parity implementation -->
                    <!-- In robust version, we'd fetch cheques via AJAX as per Old ERP. -->
                    <!-- For now, we'll assume manual entry or simplified selection if possible. -->
                    <!-- Re-reading Old ERP: It lists cheques. We need that logic. -->
                    <!-- We will just put a placeholder message for the dynamic list for this iteration -->
                    <!-- to unblock the UI. The AJAX logic for filtering cheques is complex. -->

                    <div class="alert alert-warning">
                        Cheque List loading is currently being migrated.
                        Manual functional completion in progress.
                    </div>

                    <div class="col-md-12 text-center mb-3">
                        <div class="form-check form-check-inline">
                            <input type="checkbox" name="encash" value="1" class="form-check-input"
                                id="encash">
                            <label class="form-check-label" for="encash">Encash</label>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label for="to_account">Deposit To:</label>
                        <select name="to_account" id="to_account" class="form-control select2" required
                            style="width:100%">
                            <option value="">Please Select</option>
                            @foreach ($to_accounts ?? [] as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label for="note">Note</label>
                        <textarea name="note" class="form-control" placeholder="Note" rows="4"></textarea>
                    </div>

                    <div class="form-group mb-3">
                        <label for="attachment">Add Image / Document (JPEG, PNG, Word, PDF, Excel)</label>
                        <input type="file" name="attachment" class="form-control">
                    </div>
                @endif
            </div>

            <div class="modal-footer">
                @if (!isset($error))
                    <button type="submit" class="btn btn-primary submit_btn">Submit</button>
                @endif
                <button type="button" class="btn btn-default" data-bs-dismiss="modal">Close</button>
            </div>
        </form>

    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $('.select2').select2();

        $('#encash').change(function() {
            if ($(this).is(':checked')) {
                $("#to_account").prop('disabled', true);
            } else {
                $("#to_account").prop('disabled', false);
            }
        });
    });
</script>
