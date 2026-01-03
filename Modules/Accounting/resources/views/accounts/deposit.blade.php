<div class="modal-dialog" role="document">
    <div class="modal-content">

        <form action="{{ route('account.deposit', ['type' => $type ?? 'cash']) }}" method="post" id="deposit_form"
            enctype="multipart/form-data">
            @csrf

            <div class="modal-body">

                @if (isset($error))
                    <div class="alert alert-danger">{{ $error }}</div>
                @else
                    {{-- Cash account info --}}
                    @if ($type === 'cash' && isset($account))
                        <div class="alert alert-primary mb-1">
                            <div class="d-flex justify-content-between">
                                <strong>Cash Account:</strong> <span
                                    style="color: rgb(240, 14, 123)">{{ $account->name }}</span>
                                <span class="fw-bold text-danger">
                                    {{ number_format($account_balance, 2) }}
                                </span>
                            </div>
                            <input type="hidden" name="from_account" value="{{ $account->id }}">
                        </div>
                    @endif
                    <div class="row">
                        <div class="col-md-12 mb-1">
                            <!-- Placeholder for Cheque List filtering if needed, for now simplified -->
                            <div class="alert alert-info">Select 'Deposit Account' to deposit money.</div>
                        </div>
                    </div>

                    <div class="row">

                        {{-- Deposit FROM (only for card/others) --}}
                        @if ($type !== 'cash')
                            <div class="form-group mb-1">
                                <label>Deposit From *</label>
                                <select name="from_account" class="form-control select2" required style="width:100%">
                                    <option value="">Please Select</option>
                                    @foreach ($from_accounts as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        {{-- Deposit TO --}}
                        <div class="form-group mb-1">
                            <label for="to_account">Deposit To:</label>
                            <select name="to_account_id" class="form-control select2" required style="width:100%">
                                <option value="">Please Select</option>
                                @foreach ($to_accounts as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>


                        <div class="col-sm-6 mb-1">
                            <label>Date *</label>
                            <input type="date" name="operation_date" class="form-control" required
                                value="{{ now()->format('Y-m-d') }}">
                        </div>
                        <div class="col-sm-6 mb-1">
                            <label>Amount *</label>
                            <input type="number" name="amount" class="form-control" required step="any"
                                placeholder="Amount">
                        </div>
                    </div>

                    <div class="mb-1">
                        <label>Note</label>
                        <textarea name="note" class="form-control" rows="2"></textarea>
                    </div>

                    <div class="form-group mb-1">
                        <label for="attachment">Add Image / Document (JPEG, PNG, Word, PDF, Excel)</label>
                        <input type="file" name="attachment" class="form-control">
                    </div>

                @endif
            </div>

            <div class="modal-footer">
                @if (!isset($error))
                    <button type="submit" class="btn btn-primary">Submit</button>
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
            dropdownParent: $('.modal')
        });
    });
</script>
