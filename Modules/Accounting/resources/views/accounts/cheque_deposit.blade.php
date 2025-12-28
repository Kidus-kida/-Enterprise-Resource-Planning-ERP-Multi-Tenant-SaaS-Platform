<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">

        <form action="{{ route('account.post-cheque-deposit') }}" method="post" id="deposit_form"
            enctype="multipart/form-data">
            @csrf

            <div class="modal-body">
                @if (isset($error))
                    <div class="alert alert-danger">{{ $error }}</div>
                @else
                    {{-- Selected Account --}}
                    <div class="alert alert-primary p-2 mb-2">
                        <strong>Selected Account: <br></strong> <span style="color: red">{{ $account->name ?? '' }}</span>
                        <input type="hidden" name="account_id" value="{{ $account->id ?? '' }}">
                    </div>

                    {{-- Transaction Date --}}
                    <div class="form-group mb-2">
                        <label>Transaction Date *</label>
                        <input type="date" name="operation_date" class="form-control" required
                            value="{{ now()->format('Y-m-d') }}">
                    </div>

                    {{-- Amount --}}
                    <div class="form-group mb-2">
                        <label>Amount *</label>
                        <input type="number" name="amount" class="form-control" step="0.01" min="0.01"
                            required>
                    </div>

                    {{-- Info --}}
                    <div class="alert alert-info mb-2">
                        Deposit or encash cheques currently in hand.
                    </div>

                    {{-- Encash --}}
                    <div class="text-center mb-2">
                        <div class="form-check form-check-inline">
                            <input type="checkbox" name="encash" value="1" class="form-check-input"
                                id="encash">
                            <label class="form-check-label" for="encash">
                                Encash (Deposit to Cash)
                            </label>
                        </div>
                    </div>

                    {{-- Deposit To --}}
                    <div class="form-group mb-2">
                        <label>Deposit To *</label>
                        <select name="to_account" id="to_account" class="form-control select2" required
                            style="width:100%">
                            <option value="">Please Select</option>
                            @foreach ($to_accounts ?? [] as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Note --}}
                    <div class="form-group mb-2">
                        <label>Note</label>
                        <textarea name="note" class="form-control" rows="2"></textarea>
                    </div>

                    {{-- Attachment --}}
                    <div class="form-group mb-2">
                        <label>Attachment</label>
                        <input type="file" name="attachment" class="form-control"
                            accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx">
                    </div>

                @endif
            </div>

            <div class="modal-footer">
                @if (!isset($error))
                    <button type="submit" class="btn btn-primary submit_btn">
                        Submit
                    </button>
                @endif
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Close
                </button>
            </div>
        </form>
    </div>
</div>
<script>
    $(document).ready(function() {

        $('.select2').select2({
            dropdownParent: $('#deposit_form')
        });

        $('#encash').change(function() {
            if ($(this).is(':checked')) {
                $('#to_account')
                    .prop('disabled', true)
                    .prop('required', false);
            } else {
                $('#to_account')
                    .prop('disabled', false)
                    .prop('required', true);
            }
        });

    });
</script>
