<div class="row">
    <h4 class="card-title text-primary">Prefix Settings</h4>
    <p class="text-muted">Configure prefix default settings.</p>
    @php
        $prefixes = [
            'purchase' => 'Purchase Orders',
            'purchase_return' => 'Purchase Returns',
            'stock_transfer' => 'Stock Transfer',
            'stock_adjustment' => 'Stock Adjustment',
            'sell_return' => 'Sell Return',
            'expense' => 'Expenses',
            'contacts' => 'Contacts',
            'purchase_payment' => 'Purchase Payment',
            'sell_payment' => 'Sell Payment',
            'expense_payment' => 'Expense Payments',
            'business_location' => 'Business Location',
            'username' => 'Username',
            'subscription' => 'Subscription No.',
            'customer' => 'Customer',
            'supplier' => 'Supplier',
            'settlement' => 'Settlement',
            'excess_commission' => 'Excess Commission',
            'shortage_recover' => 'Shortage Recover',
            'security_deposit' => 'Security Deposits',
            'refund_security_deposit' => 'Refund Security Deposits',
            'employee_no' => 'Employee No',
            'route_no' => 'Route Operation No.',
        ];
    @endphp

    @foreach ($prefixes as $key => $label)
        <div class="col-md-4">
            <div class="form-group mb-3">
                <label class="form-label fw-bold">{{ $label }}:</label>
                <div class="input-group mb-2">
                    <span class="input-group-text">Prefix</span>
                    <input type="text" name="ref_no_prefixes[{{ $key }}]" class="form-control"
                        value="{{ $business->ref_no_prefixes[$key] ?? '' }}">
                </div>
                <div class="input-group">
                    <span class="input-group-text">Starting No.</span>
                    <input type="text" name="ref_no_starting_number[{{ $key }}]" class="form-control"
                        value="{{ $business->ref_no_starting_number[$key] ?? '' }}">
                </div>
            </div>
        </div>
    @endforeach
</div>
