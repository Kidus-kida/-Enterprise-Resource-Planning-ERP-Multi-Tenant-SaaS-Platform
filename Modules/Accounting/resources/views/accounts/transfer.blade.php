<div class="modal-dialog" role="document">
    <div class="modal-content">

        <form action="{{ route('accounting.fund_transfer.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Fund Transfer</h4>
            </div>

            <div class="modal-body">

                <!-- Selected account -->
                <div class="form-group">
                    <strong>Selected Account</strong> :
                    {{ $from_account->name }}

                    <span class="text-red pull-right">
                        Balance:
                        {{ !empty($account_balance->balance) ? $account_balance->balance : '0.00' }}
                    </span>

                    <input type="hidden" name="from_account" value="{{ $from_account->id }}">
                    <input type="hidden" id="check_insufficient" value="{{ $check_insufficient }}">
                    <input type="hidden" id="account_balance"
                        value="{{ !empty($account_balance->balance) ? $account_balance->balance : 0 }}">
                </div>

                <!-- Account group -->
                <div class="form-group">
                    <label>Account Group *</label>
                    <select name="account_group_id" id="account_group_id" class="form-control select2">
                        <option value="">Please select</option>
                        @foreach ($account_groups as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Transfer to -->
                <div class="form-group">
                    <label>Transfer To *</label>
                    <select name="to_account" id="to_account" class="form-control select2" required>
                        <option value="">Please select</option>
                        @foreach ($to_accounts as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Amount -->
                <div class="form-group">
                    <label>Amount *</label>
                    <input type="number" name="amount" id="amount" class="form-control input_number" value="0"
                        required>
                </div>

                <!-- Cheque number -->
                <div class="form-group">
                    <label>Cheque Number *</label>
                    <input type="text" name="cheque_number" class="form-control" required>
                </div>

                <!-- Date -->
                <div class="form-group">
                    <label class="date_label">Cheque Date *</label>
                    <div class="input-group date" id="od_datetimepicker">
                        <input type="date" name="operation_date" class="form-control" required>
                        <span class="input-group-addon">
                            <span class="glyphicon glyphicon-calendar"></span>
                        </span>
                    </div>
                </div>

                <!-- Transfer type -->
                {{-- <div class="row">
          <div class="col-md-6">
            <label>
              <input type="radio"
                     name="transfer_or_cheque"
                     class="transfer_or_cheque"
                     value="cheque"
                     checked>
              Cheque
            </label>
          </div>
          <div class="col-md-6">
            <label>
              <input type="radio"
                     name="transfer_or_cheque"
                     class="transfer_or_cheque"
                     value="transfer">
              Transfer
            </label>
          </div>
        </div> --}}

                <hr>

                <!-- Post dated cheque options -->
                @if (!empty($pacakge_details['show_post_dated_cheque']))
                    <div class="row pd-fields">
                        <div class="col-md-6">
                            <label>
                                <input type="checkbox" name="post_dated_cheque" value="1" id="post_dated_cheque">
                                Post Dated Cheque
                            </label>
                        </div>

                        @if (!empty($pacakge_details['update_post_dated_cheque']))
                            <div class="col-md-6">
                                <label>
                                    <input type="checkbox" name="update_post_dated_cheque" value="1"
                                        id="update_post_dated_cheque">
                                    Update Post Dated Cheque
                                </label>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Note -->
                <div class="form-group">
                    <label>Note</label>
                    <textarea name="note" class="form-control" rows="3"></textarea>
                </div>

                <!-- Attachment -->
                <div class="form-group">
                    <label>Attachment</label>
                    <input type="file" name="attachment">
                </div>

            </div>

            <!-- Footer -->
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary submit_btn">
                    Submit
                </button>
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    Close
                </button>
            </div>

        </form>

    </div>
</div>
