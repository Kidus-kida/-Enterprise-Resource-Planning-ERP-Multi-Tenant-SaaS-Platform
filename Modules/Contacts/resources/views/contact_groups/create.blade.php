<div class="modal-body">
    <form action="{{ route('contact-groups.store') }}" method="post" id="contact_group_add_form">
        @csrf
        <div class="row">
           <div class="col-md-12">
                <div class="input-block mb-3">
                    <x-form.label>{{ __('Name') }} <span class="text-danger">*</span></x-form.label>
                    <x-form.input type="text" name="name" required />
                </div>
           </div>
           
           <div class="col-md-12">
                <div class="input-block mb-3">
                    <x-form.label>{{ __('Type') }}</x-form.label>
                    <select class="form-control select" name="type" id="type">
                        @foreach($types as $key => $value)
                            <option value="{{ $key }}" @if(($type ?? '') == $key) selected @endif>{{ $value }}</option>
                        @endforeach
                    </select>
                </div>
           </div>

           <div class="col-md-12 percentage-field hide-supplier">
                <div class="input-block mb-3">
                    <x-form.label>{{ __('Calculation Percentage') }}</x-form.label>
                    <x-form.input type="number" name="amount" step="0.01" />
                </div>
           </div>

           <!-- Missing Selling Price Group dropdown implementation (empty for now) -->
           <!-- 
           <div class="col-md-12 selling_price_group-field hide hide-supplier">
               ...
           </div> 
           -->

           <div class="col-md-12 hide-supplier">
                <div class="input-block mb-3">
                    <x-form.label>{{ __('Account Type') }}</x-form.label>
                    <select name="account_type_id" class="form-control select" id="changeAccountSelect">
                        <option value="">{{ __('Select Account Type') }}</option>
                        @foreach($allAccountsType as $accountType)
                            <option value="{{ $accountType->id }}">{{ $accountType->name }}</option>
                        @endforeach
                    </select>
                </div>
           </div>

           <div class="col-md-12 hide-supplier">
                <div class="input-block mb-3">
                     <x-form.label id="interest_account_label">{{ __('Interest Income Account') }}</x-form.label>
                    <select name="interest_account_id" class="form-control select" id="AccountName">
                        <option value="">{{ __('Select Account') }}</option>
                        @foreach($allAccounts as $account)
                             <option value="{{ $account->id }}">{{ $account->name }}</option>
                        @endforeach
                    </select>
                </div>
           </div>
        </div>
        <div class="submit-section">
            <x-form.button class="btn btn-primary submit-btn">{{ __('Submit') }}</x-form.button>
        </div>
    </form>
</div>
<script>
    $(document).ready(function() {
        // Initial check
        var initialType = $("#type").val();
        handleTypeChange(initialType);

        $("#type").change(function () {
            var typeId = $(this).val();
            handleTypeChange(typeId);
        });

        function handleTypeChange(typeId) {
            if(typeId == 'supplier'){
                $(".hide-supplier").hide();
                 $("#interest_account_label").text("{{ __('Interest Expense Account') }}");
            }else{
                $(".hide-supplier").show();
                 $("#interest_account_label").text("{{ __('Interest Income Account') }}");
            }
        }

        $("#changeAccountSelect").change(function () {
            var typeId = $(this).val();
            $.ajax({
                type: "POST",
                url: "{{ route('contact-groups.fetch-account') }}",
                data: {type_id: typeId, _token: '{{csrf_token()}}'},
                dataType: "json",
                success: function (data) {
                    $("#AccountName").html('<option value="">{{ __("Select Account") }}</option>');
                    $.each(data.accounts, function (key, value) {
                        $("#AccountName").append('<option value="' + value.id + '">' + value.name + '</option>');
                    });
                }
            });
        });
    });
</script>
