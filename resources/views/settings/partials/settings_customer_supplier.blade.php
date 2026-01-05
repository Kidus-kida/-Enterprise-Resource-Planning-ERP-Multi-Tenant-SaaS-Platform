<div class="row">
    <h4 class="card-title text-primary">Customer Supplier Settings</h4>
    <p class="text-muted">Configure customer supplier default settings.</p>
    <div class="col-sm-12">
        <ul class="nav nav-tabs nav-tabs-solid nav-justified" id="contactTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="customer-tab" data-bs-toggle="tab" href="#customer" role="tab">
                    <i class="fa fa-address-book"></i> <strong>customer</strong>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="supplier-tab" data-bs-toggle="tab" href="#supplier" role="tab">
                    <i class="fa fa-address-book"></i> <strong>supplier</strong>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="property-customer-tab" data-bs-toggle="tab" href="#property_customer"
                    role="tab">
                    <i class="fa fa-address-book"></i> <strong>property_customer</strong>
                </a>
            </li>
        </ul>

        <div class="tab-content pt-3">
            <!-- Customer Tab -->
            <div class="tab-pane fade show active" id="customer" role="tabpanel">
                <h4>select_the_field_you_want_in_adding_contact</h4>
                <div class="row">
                    @php
                        $customer_fields = [
                            'customer_type' => __('lang_v1.type'),
                            'customer_name' => __('lang_v1.name'),
                            'customer_contact_id' => __('lang_v1.contact_id'),
                            'customer_tax_number' => __('lang_v1.tax_number'),
                            'customer_opening_balance' => __('lang_v1.opening_balance'),
                            'customer_pay_term' => __('lang_v1.pay_term'),
                            'customer_transaction_date' => __('lang_v1.transaction_date'),
                            'customer_customer_group' => __('lang_v1.customer_group'),
                            'customer_credit_limit' => __('lang_v1.credit_limit'),
                            'customer_password' => __('lang_v1.password'),
                            'customer_confirm_password' => __('lang_v1.confirm_password'),
                            'customer_email' => __('lang_v1.email'),
                            'customer_mobile' => __('lang_v1.mobile'),
                            'customer_alternate_contact_number' => __('lang_v1.alternate_contact_number'),
                            'customer_landline' => __('lang_v1.landline'),
                            'customer_address' => __('lang_v1.address'),
                            'customer_city' => __('lang_v1.city'),
                            'customer_state' => __('lang_v1.state'),
                            'customer_country' => __('lang_v1.country'),
                            'customer_landmark' => __('lang_v1.landmark'),
                            'customer_custom_field_1' => __('lang_v1.custom_field_1'),
                            'customer_custom_field_2' => __('lang_v1.custom_field_2'),
                            'customer_custom_field_3' => __('lang_v1.custom_field_3'),
                            'customer_custom_field_4' => __('lang_v1.custom_field_4'),
                        ];

                        $default_checked = [
                            'customer_type',
                            'customer_name',
                            'customer_contact_id',
                            'customer_opening_balance',
                            'customer_transaction_date',
                        ];
                    @endphp

                    @foreach ($customer_fields as $key => $label)
                        <div class="col-sm-4">
                            <div class="form-group mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox"
                                        name="contact_fields[{{ $key }}]" value="1"
                                        {{ in_array($key, $default_checked) || array_key_exists($key, $business->contact_fields ?? []) ? 'checked' : '' }}
                                        {{ in_array($key, $default_checked) ? 'onclick="return false;"' : '' }}>
                                    <label class="form-check-label">{{ $label }}</label>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Supplier Tab -->
            <div class="tab-pane fade" id="supplier" role="tabpanel">
                <h4>select_the_field_you_want_in_adding_contact</h4>
                <div class="row">
                    @php
                        $supplier_fields = [
                            'supplier_type' => __('lang_v1.type'),
                            'supplier_name' => __('lang_v1.name'),
                            'supplier_contact_id' => __('lang_v1.contact_id'),
                            'supplier_tax_number' => __('lang_v1.tax_number'),
                            'supplier_opening_balance' => __('lang_v1.opening_balance'),
                            'supplier_pay_term' => __('lang_v1.pay_term'),
                            'supplier_transaction_date' => __('lang_v1.transaction_date'),
                            'supplier_supplier_group' => __('lang_v1.supplier_group'),
                            'supplier_email' => __('lang_v1.email'),
                            'supplier_mobile' => __('lang_v1.mobile'),
                            'supplier_alternate_contact_number' => __('lang_v1.alternate_contact_number'),
                            'supplier_landline' => __('lang_v1.landline'),
                            'supplier_address' => __('lang_v1.address'),
                            'supplier_city' => __('lang_v1.city'),
                            'supplier_state' => __('lang_v1.state'),
                            'supplier_country' => __('lang_v1.country'),
                            'supplier_landmark' => __('lang_v1.landmark'),
                            'supplier_custom_field_1' => __('lang_v1.custom_field_1'),
                            'supplier_custom_field_2' => __('lang_v1.custom_field_2'),
                            'supplier_custom_field_3' => __('lang_v1.custom_field_3'),
                            'supplier_custom_field_4' => __('lang_v1.custom_field_4'),
                        ];

                        $supplier_default_checked = [
                            'supplier_type',
                            'supplier_name',
                            'supplier_contact_id',
                            'supplier_opening_balance',
                            'supplier_transaction_date',
                        ];
                    @endphp

                    @foreach ($supplier_fields as $key => $label)
                        <div class="col-sm-4">
                            <div class="form-group mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox"
                                        name="contact_fields[{{ $key }}]" value="1"
                                        {{ in_array($key, $supplier_default_checked) || array_key_exists($key, $business->contact_fields ?? []) ? 'checked' : '' }}
                                        {{ in_array($key, $supplier_default_checked) ? 'onclick="return false;"' : '' }}>
                                    <label class="form-check-label">{{ $label }}</label>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Property Customer Tab -->
            <div class="tab-pane fade" id="property_customer" role="tabpanel">
                <h4>select_the_field_you_want_in_adding_contact</h4>
                <div class="row">
                    @php
                        $property_fields = [
                            'property_customer_type' => __('lang_v1.type'),
                            'property_customer_name' => __('lang_v1.name'),
                            'property_customer_contact_id' => __('lang_v1.contact_id'),
                            'property_customer_tax_number' => __('lang_v1.tax_nic_passport_number'),
                            'property_customer_opening_balance' => __('lang_v1.opening_balance'),
                            'property_customer_pay_term' => __('lang_v1.pay_term'),
                            'property_customer_transaction_date' => __('lang_v1.transaction_date'),
                            'property_customer_customer_group' => __('lang_v1.customer_group'),
                            'property_customer_password' => __('lang_v1.password'),
                            'property_customer_confirm_password' => __('lang_v1.confirm_password'),
                            'property_customer_email' => __('lang_v1.email'),
                            'property_customer_mobile' => __('lang_v1.mobile'),
                            'property_customer_alternate_contact_number' => __('lang_v1.alternate_contact_number'),
                            'property_customer_landline' => __('lang_v1.landline'),
                            'property_customer_address' => __('lang_v1.address'),
                            'property_customer_city' => __('lang_v1.city'),
                            'property_customer_state' => __('lang_v1.state'),
                            'property_customer_country' => __('lang_v1.country'),
                            'property_customer_landmark' => __('lang_v1.landmark'),
                            'property_customer_custom_field_1' => __('lang_v1.custom_field_1'),
                            'property_customer_custom_field_2' => __('lang_v1.custom_field_2'),
                            'property_customer_custom_field_3' => __('lang_v1.custom_field_3'),
                            'property_customer_custom_field_4' => __('lang_v1.custom_field_4'),
                        ];
                        $property_default_checked = [
                            'property_customer_type',
                            'property_customer_name',
                            'property_customer_contact_id',
                            'property_customer_opening_balance',
                            'property_customer_transaction_date',
                            'property_customer_password',
                            'property_customer_confirm_password',
                            'property_customer_email',
                            'property_customer_mobile',
                            'property_customer_alternate_contact_number',
                            'property_customer_landline',
                            'property_customer_address',
                            'property_customer_city',
                            'property_customer_state',
                            'property_customer_country',
                        ];
                    @endphp

                    @foreach ($property_fields as $key => $label)
                        <div class="col-sm-4">
                            <div class="form-group mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox"
                                        name="contact_fields[{{ $key }}]" value="1"
                                        {{ in_array($key, $property_default_checked) || array_key_exists($key, $business->contact_fields ?? []) ? 'checked' : '' }}
                                        {{ in_array($key, $property_default_checked) ? 'onclick="return false;"' : '' }}>
                                    <label class="form-check-label">{{ $label }}</label>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            @if (!empty($business_settings->captch_site_key))
                <div class="col-md-12">
                    <div class="form-group mt-3">
                        <div class="g-recaptcha" data-sitekey="{{ $business_settings->captch_site_key }}"></div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
