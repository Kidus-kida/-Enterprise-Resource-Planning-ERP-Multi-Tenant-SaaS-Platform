<div class="modal-body">
    <form action="{{ route('clients.store') }}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-4">
                        <div class="input-block mb-3">
                            <x-form.label>{{ __('Full Name') }}</x-form.label>
                            <x-form.input type="text" name="full_name" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="input-block mb-3">
                            <x-form.label>{{ __('Need to send SMS') }}</x-form.label>
                            <x-form.select name="need_sms">
                                <option value="" disabled selected>-- Select --</option>
                                <option value="yes">Yes</option>
                                <option value="no">No</option>
                            </x-form.select>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="input-block mb-3">
                            <x-form.label>{{ __('Credit Notification Type') }}</x-form.label>
                            <x-form.select name="credit_notification_type">
                                <option value="none" disabled selected>None</option>
                                <option value="settlement">Settlement</option>
                                <option value="VAT_invoice">VAT Invoice</option>
                                <option value="pumper_dashboard">Pumper Dashboard</option>
                            </x-form.select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="input-block mb-3">
                            <x-form.label>{{ __('Contact ID') }}</x-form.label>
                            <x-form.input type="text" name="contact_ID" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="input-block mb-3">
                            <x-form.label>{{ __('Tax number') }}</x-form.label>
                            <x-form.input type="email" name="Tax_number" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="input-block mb-3">
                            <x-form.label>{{ __('VAT No') }}</x-form.label>
                            <x-form.input type="email" name="VAT_No" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="input-block mb-3">
                            <x-form.label>{{ __('Opening Balance') }}</x-form.label>
                            <x-form.input type="number" placeholder="0" name="opening_balance" />
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="input-block mb-3">
                            <label>{{ __('Phone Number') }}</label>
                            <x-form.phone type="text" name="phone" />
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <x-form.input-block>
                            <x-form.label>
                                {{ __('Password') }}
                            </x-form.label>
                            <x-form.input type="password" name="password" />
                        </x-form.input-block>
                    </div>
                    <div class="col-sm-6">
                        <x-form.input-block>
                            <x-form.label>
                                {{ __('Confirm Password') }}
                            </x-form.label>
                            <x-form.input type="password" name="password_confirmation" />
                        </x-form.input-block>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="input-block mb-3">
                    <x-form.label>{{ __('Address') }}</x-form.label>
                    <x-form.input type="text" name="address" />
                </div>
            </div>

        </div>
        <div class="row">
            <div class="input-block mb-3">
                <label class="col-form-label">{{ __('Avatar') }}</label>
                <x-form.input type="file" name="avatar" />
            </div>
            <div class="status-toggle">
                <x-form.input type="checkbox" id="status" class="check" name="status" />
                <label for="status" class="checktoggle">checkbox</label>
            </div>
        </div>
        <div class="submit-section">
            <x-form.button class="btn btn-primary submit-btn">{{ __('Submit') }}</x-form.button>
        </div>
    </form>
</div>