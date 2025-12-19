<div class="modal-dialog" role="document">
    <div class="modal-content">

        <form action="{{ route('account.deposit', ['type' => $type ?? 'cash']) }}" method="post" id="deposit_form"
            enctype="multipart/form-data">
            @csrf
            <div class="modal-header">
                <h4 class="modal-title">Deposit</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                @if (isset($error))
                    <div class="alert alert-danger">{{ $error }}</div>
                @else
                    <div class="alert alert-primary mb-3" role="alert">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>Selected Account:</strong> {{ $account->name ?? '' }}
                            </div>
                            <div class="fw-bold">
                                Balance:
                                <span class="text-danger">
                                    @if (isset($account_balance))
                                        {{ number_format($account_balance, 2) }}
                                    @else
                                        0.00
                                    @endif
                                </span>
                            </div>
                        </div>
                        <input type="hidden" name="to_account_id" value="{{ $account->id ?? '' }}">
                    </div>

                    <div class="row">
                        <div class="col-sm-6 mb-3">
                            <div class="form-group">
                                <label for="from_account">Deposit From:</label>
                                <select name="from_account" class="form-control select2" required style="width:100%">
                                    <option value="">Please Select</option>
                                    @foreach ($from_accounts ?? [] as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-sm-6 mb-3">
                            <div class="form-group">
                                <label for="operation_date">Date:*</label>
                                <input type="date" name="operation_date" class="form-control" required
                                    value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}">
                            </div>
                        </div>
                    </div>

                    <div class="row" id="amounts_row">
                        <div class="col-sm-12 mb-3">
                            <label for="amount">Amount:*</label>
                            <input type="number" name="amount" class="form-control input_amount" required
                                placeholder="Amount" step="any">
                        </div>
                    </div>

                    <hr>

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
        $(".select2").select2();
    });
</script>
