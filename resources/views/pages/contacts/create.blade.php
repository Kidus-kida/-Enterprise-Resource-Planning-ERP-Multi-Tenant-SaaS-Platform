<div class="modal-body">
    <form action="{{ route('contacts.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="row">
            {{-- Contact Type --}}
            <div class="col-md-3">
                <div class="input-block mb-3">
                    <label>Contact Type <span class="text-danger">*</span></label>
                    <select name="contact_type" id="contact_type" class="form-control">
                        @foreach($types as $key => $value)
                            <option value="{{ $key }}">{{ $value }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Name --}}
            <div class="col-md-3">
                <div class="input-block mb-3">
                    <label>Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required>
                </div>
            </div>

            {{-- Need to send SMS --}}
            <div class="col-md-3">
                <div class="input-block mb-3">
                    <label>Need to send SMS <span class="text-danger">*</span></label>
                    <select name="send_sms" class="form-control">
                        <option value="">Please Select</option>
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                </div>
            </div>

            {{-- Credit Notification Type --}}
            <div class="col-md-3 customer_fields">
                <div class="input-block mb-3">
                    <label>Credit Notification Type <span class="text-danger">*</span></label>
                    <select name="credit_notification_type" class="form-control">
                        <option value="none">None</option>
                        <option value="settlement">Settlement</option>
                        <option value="vat">VAT</option>
                        <option value="invoice">Invoice</option>
                    </select>
                </div>
            </div>

            {{-- Sub Customer --}}
            <div class="col-md-12 mb-2 customer_fields">
                <div class="form-check">
                    <input type="checkbox" name="is_sub_customer" class="form-check-input" id="subCustomer">
                    <label class="form-check-label" for="subCustomer">Sub Customer</label>
                </div>
            </div>

            {{-- Contact ID --}}
            <div class="col-md-3">
                <div class="input-block mb-3">
                    <label>Contact ID</label>
                    <input type="text" name="contact_id" class="form-control" value="{{ $contact_id }}">
                </div>
            </div>

            {{-- Tax Number --}}
            <div class="col-md-3">
                <div class="input-block mb-3">
                    <label>Tax Number</label>
                    <input type="text" name="tax_number" class="form-control">
                </div>
            </div>

            {{-- VAT No --}}
            <div class="col-md-3">
                <div class="input-block mb-3">
                    <label>VAT No</label>
                    <input type="text" name="vat_no" class="form-control">
                </div>
            </div>

            {{-- Opening Balance --}}
            <div class="col-md-3">
                <div class="input-block mb-3">
                    <label>Opening Balance</label>
                    <input type="number" name="opening_balance" class="form-control" value="0">
                </div>
            </div>

            {{-- Pay Term / Supplier Group / Transaction Date --}}
            <div class="col-md-4">
                <div class="input-block mb-3">
                    <label>
                        Pay Term
                        <i class="fa fa-info-circle text-muted" data-bs-toggle="tooltip"
                           title="Payment duration for this contact"></i>
                    </label>
                    <div class="d-flex gap-2">
                        <input type="number" name="pay_term_value" class="form-control" placeholder="Pay term">
                        <select name="pay_term_type" class="form-control">
                            <option value="">Please Select</option>
                            <option value="days">Days</option>
                            <option value="months">Months</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Customer Group --}}
            <div class="col-md-4 customer_fields">
                <div class="input-block mb-3">
                    <label>Customer Group</label>
                    <select name="customer_group_id" class="form-control">
                        <option value="">Please Select</option>
                         @foreach($customer_groups as $key => $value)
                            <option value="{{ $key }}">{{ $value }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Supplier Group --}}
            <div class="col-md-4 supplier_fields">
                <div class="input-block mb-3">
                    <label>Supplier Group</label>
                    <select name="supplier_group_id" class="form-control">
                        <option value="">Please Select</option>
                        @foreach($supplier_groups as $key => $value)
                            <option value="{{ $key }}">{{ $value }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-md-4">
                <div class="input-block mb-3">
                    <label>Transaction Date</label>
                    <input type="date" name="transaction_date" class="form-control">
                </div>
            </div>

            {{-- Add more mobile numbers --}}
            <div class="col-md-12 mb-2">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="moreMobile">
                    <label class="form-check-label" for="moreMobile">
                        Add More Mobile Numbers for the SMS Notification
                    </label>
                </div>
            </div>

            {{-- Email --}}
            <div class="col-md-3">
                <div class="input-block mb-3">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control">
                </div>
            </div>

            {{-- Mobile --}}
            <div class="col-md-3">
                <div class="input-block mb-3">
                    <label>Mobile <span class="text-danger">*</span></label>
                    <input type="text" name="mobile" class="form-control" required>
                </div>
            </div>

            {{-- Alternate Number --}}
            <div class="col-md-3">
                <div class="input-block mb-3">
                    <label>Alternate Contact Number</label>
                    <input type="text" name="alternate_mobile" class="form-control">
                </div>
            </div>

            {{-- Landline --}}
            <div class="col-md-3">
                <div class="input-block mb-3">
                    <label>Landline</label>
                    <input type="text" name="landline" class="form-control">
                </div>
            </div>

            {{-- Assigned To --}}
            <div class="col-md-3">
                <div class="input-block mb-3">
                    <label>Assigned To</label>
                    <select name="assigned_to" class="form-control">
                        <option value="">None</option>
                        @foreach($user_groups as $key => $value)
                            <option value="{{ $key }}">{{ $value }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Vehicle No --}}
            <div class="col-md-3">
                <div class="input-block mb-3">
                    <label>Vehicle No</label>
                    <input type="text" name="vehicle_no" class="form-control">
                </div>
            </div>

            {{-- Address --}}
            <div class="col-md-12">
                <div class="input-block mb-3">
                    <label>Address</label>
                    <input type="text" name="address" class="form-control">
                </div>
            </div>

            {{-- Address Line 2 --}}
            <div class="col-md-12">
                <div class="input-block mb-3">
                    <label>Address Line 2</label>
                    <input type="text" name="address_line_2" class="form-control">
                </div>
            </div>

            {{-- Address Line 3 --}}
            <div class="col-md-12">
                <div class="input-block mb-3">
                    <label>Address Line 3</label>
                    <input type="text" name="address_line_3" class="form-control">
                </div>
            </div>

            {{-- City --}}
            <div class="col-md-3">
                <div class="input-block mb-3">
                    <label>City</label>
                    <input type="text" name="city" class="form-control">
                </div>
            </div>

            {{-- State --}}
            <div class="col-md-3">
                <div class="input-block mb-3">
                    <label>State</label>
                    <input type="text" name="state" class="form-control">
                </div>
            </div>

            {{-- Country --}}
            <div class="col-md-3">
                <div class="input-block mb-3">
                    <label>Country</label>
                    <input type="text" name="country" class="form-control">
                </div>
            </div>

            {{-- Landmark --}}
            <div class="col-md-3">
                <div class="input-block mb-3">
                    <label>Landmark</label>
                    <input type="text" name="landmark" class="form-control">
                </div>
            </div>

            {{-- Password Fields (Customer only) --}}
            <div class="col-md-4 customer_fields">
                <div class="input-block mb-3">
                    <label>Password <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa fa-key"></i></span>
                        <input type="password" name="password" class="form-control" placeholder="Password">
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 customer_fields">
                <div class="input-block mb-3">
                    <label>Confirm Password <span class="text-danger">*</span></label>
                    <div class="input-group">
                         <span class="input-group-text"><i class="fa fa-key"></i></span>
                        <input type="password" name="confirm_password" class="form-control" placeholder="Confirm Password">
                    </div>
                </div>
            </div>

            {{-- Passport/NIC, Image, Signature (Customer Fields) --}}
            <div class="col-md-12 customer_fields">
                 <div class="row">
                     <div class="col-md-4">
                        <div class="input-block mb-3">
                            <label>Passport No</label>
                            <input type="text" name="nic_number" class="form-control" placeholder="Passport No">
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="input-block mb-3">
                            <label>Passport Image</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="input-block mb-3">
                            <label>Signature</label>
                            <input type="file" name="signature" class="form-control" accept="image/*">
                        </div>
                    </div>
                 </div>
            </div>

        </div>

        {{-- Footer --}}
        <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Save</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
    </form>
</div>
<script>
    $(document).ready(function() {
        // Function to toggle fields based on contact type
        function toggleContactFields() {
            var contactType = $('#contact_type').val();

            if (contactType == 'customer') {
                $('.supplier_fields').hide();
                $('.customer_fields').show();
                $('input[name="password"]').prop('required', true);
                $('input[name="confirm_password"]').prop('required', true);
            } else if (contactType == 'supplier') {
                $('.customer_fields').hide();
                $('.supplier_fields').show();
                $('input[name="password"]').prop('required', false);
                 $('input[name="confirm_password"]').prop('required', false);
            } else if (contactType == 'both')  {
                $('.supplier_fields').show();
                $('.customer_fields').show();
                $('input[name="password"]').prop('required', true);
                $('input[name="confirm_password"]').prop('required', true);
            } else {
                 $('.supplier_fields').hide();
                 $('.customer_fields').hide();
            }
        }

        // Initial check on page load
        toggleContactFields();

        // Listen for changes
        $('#contact_type').change(function() {
            toggleContactFields();
        });
    });
</script>
