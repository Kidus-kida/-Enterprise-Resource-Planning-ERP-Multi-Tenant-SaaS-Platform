@extends('layouts.app')

@push('page-styles')
    <style>
        #product-select {
            width: 100%;
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
            padding: 0;
            font-size: 1rem;
            line-height: 1.5;
            background-color: #fff;
            background-clip: padding-box;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
            position: absolute;
            z-index: 1000;
            max-height: 200px;
            overflow-y: auto;
            display: none;
            /* Hidden by default */
            list-style: none;
            margin-top: 2px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        #product-select option {
            padding: 8px 12px;
            cursor: pointer;
        }

        #product-select option:hover {
            background-color: #f8f9fa;
            color: #007bff;
        }

        .form-group.product-search-container {
            position: relative;
        }

        .product-search-input-group {
            display: flex;
            align-items: center;
            flex-direction: row;
        }
    </style>
@endpush

@section('page-content')
    <div class="content container-fluid">

        <!-- Page Header -->
        <x-breadcrumb class="col">
            <x-slot name="title">{{ __('Purchase') }}</x-slot>
            <ul class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
                </li>
                <li class="breadcrumb-item active">
                    {{ __('Purchase') }}
                </li>
            </ul>
        </x-breadcrumb>


        <form action="{{ route('purchase.store') }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="input-block mb-3">
                                <x-form.label>{{ __('Purchase No') }}</x-form.label>
                                <x-form.input type="text" name="invoice_no" value="{{ $purchase_no }}" disabled />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="input-block mb-3">
                                <x-form.label>{{ __('Supplier') }}</x-form.label>

                                <div class="position-relative">
                                    <x-form.select id="supplierSelect" name="contact_id" class="form-control pe-5">
                                        <option value="" disabled selected>-- Select Supplier --</option>
                                        <option value="1">dd</option>
                                        @foreach ($suppliers ?? [] as $supplier)
                                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                        @endforeach
                                    </x-form.select>

                                    <!-- Overlapping plus button -->
                                    <button type="button"
                                        class="btn btn-outline-primary position-absolute end-0 top-0 h-100 d-flex align-items-center px-3"
                                        data-url="{{ route('supplier.create') }}" data-ajax-modal="true" data-size="lg"
                                        data-title="Add Supplier" style="border-radius: 0 .375rem .375rem 0;">
                                        +
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="input-block mb-3">
                                <x-form.label>{{ __('P. Invoice No') }}</x-form.label>
                                <x-form.input type="text" name="ref_no" />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="input-block mb-3">
                                <x-form.label>{{ __('Received Date') }}</x-form.label>
                                <x-form.input type="date" name="transaction_date" />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="input-block mb-3">
                                <x-form.label>{{ __('Invoice Date') }}</x-form.label>
                                <x-form.input type="date" name="invoice_date" />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="input-block mb-3">
                                <x-form.label>{{ __('Purchase Status') }}</x-form.label>
                                <x-form.select name="status">
                                    <option value="" disabled selected>-- Select Status --</option>
                                    <option value="pending">Pending</option>
                                    <option value="received">Received</option>
                                    <option value="ordered">Ordered</option>
                                </x-form.select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="input-block mb-3">
                                <x-form.label>{{ __('VAT Invoice') }}</x-form.label>
                                <x-form.select name="is_vat">
                                    <option value="" disabled selected>-- Select --</option>
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </x-form.select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="input-block mb-3">
                                <x-form.label>{{ __('Business Location') }}</x-form.label>
                                <x-form.select name="location_id">
                                    <option value="" disabled selected>-- Select Location --</option>
                                    <option value="1">29copy</option>
                                    @foreach ($businessLocation ?? [] as $location)
                                        <option value="{{ $location->id }}">{{ $location->name }}</option>
                                    @endforeach
                                </x-form.select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="input-block mb-3">
                                <x-form.label>{{ __('Store') }}</x-form.label>
                                <x-form.select name="store_id">
                                    <option value="" disabled selected>-- Select Store --</option>
                                    <option value="1">Main Store</option>
                                    @foreach ($stores ?? [] as $store)
                                        <option value="{{ $store->id }}">{{ $store->name }}</option>
                                    @endforeach
                                </x-form.select>
                            </div>
                        </div>

                        <div class="col-sm-4">
                            <x-form.input-block>
                                <x-form.label>{{ __('Pay term') }}</x-form.label>
                                <div class="d-flex align-items-center gap-0">
                                    <x-form.input type="number" name="pay_term_number" min="1"
                                        placeholder="Enter pay term" />
                                    <x-form.select name="pay_term_type">
                                        <option value="" disabled selected>-- Unit --</option>
                                        <option value="months">Months</option>
                                        <option value="days">Days</option>
                                    </x-form.select>
                                </div>
                            </x-form.input-block>
                        </div>

                        <div class="col-sm-8">
                            <x-form.label class="col-form-label">{{ __('Attach Document') }}</x-form.label>
                            <x-form.input type="file" name="document" />
                        </div>

                        <div class="form-group mb-4 product-search-container">
                            <x-form.label>{{ __('Product / Service') }}</x-form.label>
                            <div class="product-search-input-group">
                                <!-- Search box -->
                                <input type="text" id="product-filter" class="form-control"
                                    placeholder="Search product by name or code">

                                <button type="button" class="btn btn-outline-primary d-flex align-items-center py-2 px-3"
                                    style="border-radius: 0 .375rem .375rem 0;" data-bs-toggle="modal"
                                    data-bs-target="#addProductModal">
                                    <i class="fa fa-plus"></i> {{ __('Add') }}
                                </button>
                            </div>
                            <!-- Native select (scrollable list) -->
                            <select id="product-select" size="8">

                                @foreach ($products ?? [] as $product)
                                    <option value="{{ $product['id'] }}" data-name="{{ $product['name'] }}"
                                        data-code="{{ $product['id'] }}">
                                        {{ $product['name'] }} ({{ $product['id'] }})
                                    </option>
                                @endforeach
                            </select>
                        </div>



                        <div class="table-responsive mb-4">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('Product Name') }}</th>
                                        <th>{{ __('Purchase Quantity') }}</th>
                                        <th>{{ __('Available Qty') }}</th>
                                        <th>{{ __('Unit Cost') }} <br> {{ __('(Before Discount)') }}</th>
                                        <th>{{ __('Discount Percent') }}</th>
                                        <th>{{ __('Unit Cost') }} <br> {{ __('(Before Tax)') }}</th>
                                        <th>{{ __('Subtotal') }} <br> {{ __('(Before Tax)') }}</th>
                                        <th>{{ __('Product Tax') }}</th>
                                        <th>{{ __('Net Cost') }}</th>
                                        <th>{{ __('Line Total') }}</th>
                                        <th>{{ __('Profit Margin %') }}</th>
                                        <th>{{ __('Unit Selling Price (Exc. tax)') }}</th>
                                        <th>{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody id="product-table-body">
                                    <!-- Example Row 1 -->
                                    <tr>
                                        <td>2</td>
                                        <td>Lanka Kerosene Oil<br><small>(A0017L99)</small></td>
                                        <td>
                                            <input type="number" class="form-control" value="1.00" step="0.01"
                                                min="0">
                                            <select class="form-select mt-1">
                                                <option>Ltrs</option>
                                                <option>Kg</option>
                                            </select>
                                            <button class="btn btn-sm btn-outline-secondary mt-1">Free Qty</button>
                                        </td>
                                        <td><input type="text" class="form-control bg-light" value="-363.68" readonly>
                                        </td>
                                        <td><input type="number" class="form-control" value="289.1" step="0.01"
                                                min="0"></td>
                                        <td><input type="number" class="form-control" value="10" step="0.01"
                                                min="0"></td>
                                        <td><input type="number" class="form-control" value="260.19" step="0.01"
                                                min="0" readonly></td>
                                        <td><input type="number" class="form-control" value="260.19" step="0.01"
                                                min="0" readonly></td>
                                        <td>
                                            <select class="form-select">
                                                <option>GST.</option>
                                                <option>VAT</option>
                                            </select>
                                        </td>
                                        <td><input type="number" class="form-control" value="288.81" step="0.01"
                                                min="0" readonly></td>
                                        <td><input type="number" class="form-control" value="288.81" step="0.01"
                                                min="0" readonly></td>
                                        <td><input type="number" class="form-control" value="2.14" step="0.01"
                                                min="0" readonly></td>
                                        <td><input type="number" class="form-control" value="295.00" step="0.01"
                                                min="0"></td>
                                        <td><button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                        </td>
                                    </tr>

                                    <!-- Example Row 2 -->
                                    <tr>
                                        <td>1</td>
                                        <td>Lanka Auto Diesel<br><small>(A0013L99)</small></td>
                                        <td>
                                            <input type="number" class="form-control" value="1.00" step="0.01"
                                                min="0">
                                            <select class="form-select mt-1">
                                                <option>Ltrs</option>
                                                <option>Kg</option>
                                            </select>
                                            <button class="btn btn-sm btn-outline-secondary mt-1">Free Qty</button>
                                        </td>
                                        <td><input type="text" class="form-control bg-light" value="3254.41" readonly>
                                        </td>
                                        <td><input type="number" class="form-control" value="292.79" step="0.01"
                                                min="0"></td>
                                        <td><input type="number" class="form-control" value="0" step="0.01"
                                                min="0"></td>
                                        <td><input type="number" class="form-control" value="292.790000" step="0.01"
                                                min="0" readonly></td>
                                        <td><input type="number" class="form-control" value="292.79" step="0.01"
                                                min="0" readonly></td>
                                        <td>
                                            <select class="form-select">
                                                <option>GST.</option>
                                                <option>VAT</option>
                                            </select>
                                        </td>
                                        <td><input type="number" class="form-control" value="325.00" step="0.01"
                                                min="0" readonly></td>
                                        <td><input type="number" class="form-control" value="325.00" step="0.01"
                                                min="0" readonly></td>
                                        <td><input type="number" class="form-control" value="2.84" step="0.01"
                                                min="0" readonly></td>
                                        <td><input type="number" class="form-control" value="334.23" step="0.01"
                                                min="0"></td>
                                        <td><button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                        </td>
                                    </tr>

                                    <!-- Placeholder row when no items exist -->
                                    <tr id="no-items-row" style="display: none;">
                                        <td colspan="14" class="text-center">{{ __('No items added') }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <hr />
                        <div class="row mb-3">
                            <div class="col-md-6"></div>
                            <div class="col-md-6 text-end">
                                <h5 class="mb-0">
                                    <strong>{{ __('Purchase Total:') }} <span
                                            id="display-grand-total">0.00</span></strong>
                                    <input type="hidden" id="display-grand-total" value=0 name="total_before_tax">
                                </h5>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6"></div>
                            <div class="col-md-6 text-end">
                                <h5 class="mb-0">
                                    <strong>{{ __('Discount:') }} <span id="display-grand-total">0.00</span></strong>
                                    <input type="hidden" id="discount_amount" value=0 name="discount_amount">
                                </h5>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6"></div>
                            <div class="col-md-6 text-end">
                                <h5 class="mb-0">
                                    <strong>{{ __('Net Total:') }} <span id="display-grand-total">0.00</span></strong>
                                    <input type="hidden" id="final_total" value=0 name="final_total">
                                </h5>
                            </div>
                        </div>
                        <hr />

                        <div class="col-md-12">
                            <div class="input-block mb-3">
                                <x-form.label>{{ __('Additional note') }}</x-form.label>
                                <x-form.textarea name="additional_notes" />
                            </div>
                        </div>

                        <!-- Payment Methods Section -->
                        <div class="col-md-12 mb-4">
                            <h5 class="mb-3">{{ __('Payment Details') }}</h5>
                            <div id="payment-methods-container">
                                <!-- Initial payment row -->
                                <div class="payment-row row mb-3 p-3 border rounded shadow-sm">
                                    <div class="col-12 mt-2 d-flex justify-content-end">
                                        <button type="button"
                                            class="btn btn-danger btn-sm remove-payment">&times;</button>
                                    </div>
                                    <div class="col-md-4">
                                        <x-form.label>{{ __('Payment Amount') }}</x-form.label>
                                        <x-form.input type="number" step="0.01" class="form-control payment-amount"
                                            name="payments[0][amount]" value="0.00" />
                                    </div>
                                    <div class="col-md-4">
                                        <x-form.label>{{ __('Payment Method') }}</x-form.label>
                                        <x-form.select class="form-control payment-method" name="payments[0][method]">
                                            <option value="" disabled selected>{{ __('Select Method') }}</option>
                                            <option value="cash">{{ __('Cash') }}</option>
                                            <option value="own_cards">{{ __('Own Cards') }}</option>
                                            <option value="bank_transfer">{{ __('Bank Transfer') }}</option>
                                            <option value="credit_purchase">{{ __('Credit Purchase') }}</option>
                                        </x-form.select>
                                    </div>
                                    <div class="col-md-4">
                                        <x-form.label>{{ __('Accounting Module') }}</x-form.label>
                                        <x-form.select class="form-control" name="payments[0][accounting_module]">
                                            <option value="">{{ __('None') }}</option>
                                            <option value="sales">{{ __('Sales') }}</option>
                                            <option value="purchase">{{ __('Purchase') }}</option>
                                            <option value="expense">{{ __('Expense') }}</option>
                                        </x-form.select>
                                    </div>
                                    <div class="col-md-4 mt-2 cheque-fields d-none">
                                        <x-form.label>{{ __('Cheque Number') }}</x-form.label>
                                        <x-form.input type="text" class="form-control"
                                            name="payments[0][cheque_number]" />
                                    </div>
                                    <div class="col-md-4 mt-2 bank-fields d-none">
                                        <x-form.label>{{ __('Bank Name') }}</x-form.label>
                                        <x-form.input type="text" class="form-control"
                                            name="payments[0][bank_name]" />
                                    </div>
                                </div>
                            </div>

                            <button type="button" class="btn btn-outline-secondary" id="add-payment-btn">
                                <i class="fa fa-plus"></i> {{ __('Add Payment Method') }}
                            </button>
                        </div>
                        <!-- End Payment Methods -->

                    </div>
                </div>
            </div>
            <div class="submit-section">
                <x-form.button class="btn btn-primary submit-btn">{{ __('Submit') }}</x-form.button>
            </div>
        </form>
    </div>
@endsection

@push('page-script')
    <script>
        const products = @json($products ?? []);
    </script>
    <script>
        let paymentIndex = 1;

        document.getElementById('add-payment-btn').addEventListener('click', function() {
            const container = document.getElementById('payment-methods-container');

            const newRow = document.createElement('div');
            newRow.className = 'payment-row row mb-3 p-3 border rounded shadow-sm';

            newRow.innerHTML = `
            <div class="col-12 mt-2 d-flex justify-content-end">
                <button type="button" class="btn btn-danger btn-sm remove-payment">&times;</button>
            </div>
            <div class="col-md-4">
                <x-form.label>Payment Amount</x-form.label>
                <x-form.input type="number" step="0.01" class="form-control payment-amount" name="payments[${paymentIndex}][amount]" value="0.00" />
            </div>
            <div class="col-md-4">
                <x-form.label>Payment Method</x-form.label>
                <x-form.select class="form-control payment-method" name="payments[${paymentIndex}][method]">
                    <option value="" disabled selected>Select Method</option>
                    <option value="cash">Cash</option>
                    <option value="own_cards">Own Cards</option>
                    <option value="bank_transfer">Bank Transfer</option>
                    <option value="credit_purchase">Credit Purchase</option>
                </x-form.select>
            </div>
            <div class="col-md-4">
                <x-form.label>Accounting Module</x-form.label>
                <x-form.select class="form-control" name="payments[${paymentIndex}][accounting_module]">
                    <option value="">None</option>
                    <option value="sales">Sales</option>
                    <option value="purchase">Purchase</option>
                    <option value="expense">Expense</option>
                </x-form.select>
            </div>
            <div class="col-md-4 mt-2 cheque-fields d-none">
                <x-form.label>Cheque Number</x-form.label>
                <x-form.input type="text" class="form-control" name="payments[${paymentIndex}][cheque_number]"/>
            </div>
            <div class="col-md-4 mt-2 bank-fields d-none">
                <x-form.label>Bank Name</x-form.label>
                <x-form.input type="text" class="form-control" name="payments[${paymentIndex}][bank_name]"/>
            </div>
        `;

            container.appendChild(newRow);
            paymentIndex++;

            // Attach event to new select
            const newSelect = newRow.querySelector('.payment-method');
            newSelect.addEventListener('change', togglePaymentFields);
        });

        // Toggle cheque/bank fields on method change (for existing and new rows)
        function togglePaymentFields(event) {
            const row = event.target.closest('.payment-row');
            const method = event.target.value;
            const chequeFields = row.querySelector('.cheque-fields');
            const bankFields = row.querySelector('.bank-fields');

            // Hide both first
            chequeFields.classList.add('d-none');
            bankFields.classList.add('d-none');

            if (method === 'cheque') {
                chequeFields.classList.remove('d-none');
                bankFields.classList.remove('d-none');
            } else if (method === 'bank_transfer') {
                bankFields.classList.remove('d-none');
            }
        }

        // Initial event listeners
        document.querySelectorAll('.payment-method').forEach(select => {
            select.addEventListener('change', togglePaymentFields);
        });

        // Remove payment row (but not the first one)
        document.getElementById('payment-methods-container').addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-payment')) {
                const rows = document.querySelectorAll('.payment-row');
                if (rows.length > 1) {
                    e.target.closest('.payment-row').remove();
                } else {
                    alert('{{ __('At least one payment method is required.') }}');
                }
            }
        });
    </script>
    <script>
        const filterInput = document.getElementById('product-filter');
        const productSelect = document.getElementById('product-select');

        /* 🔍 Filter products with Debounce */
        let debounceTimeout;

        filterInput.addEventListener('input', function() {
            const query = this.value.toLowerCase();

            // Hide list if query is empty
            if (!query) {
                productSelect.style.display = 'none';
                return;
            }

            clearTimeout(debounceTimeout);

            debounceTimeout = setTimeout(() => {
                let hasResults = false;

                Array.from(productSelect.options).forEach(option => {
                    if (!option.value) return;

                    const text = option.text.toLowerCase();
                    const match = text.includes(query);
                    option.style.display = match ? 'block' : 'none';
                    if (match) hasResults = true;
                });

                // Show select only if we have results
                productSelect.style.display = hasResults ? 'block' : 'none';

                // Reset scroll position
                productSelect.scrollTop = 0;

                // Auto-size based on results (up to max-height defined in CSS)
                productSelect.size = Math.min(productSelect.options.length, 10);

            }, 500); // 500ms debounce delay
        });

        /* 🖱️ Close list when clicking outside */
        document.addEventListener('click', function(e) {
            if (!filterInput.contains(e.target) && !productSelect.contains(e.target)) {
                productSelect.style.display = 'none';
            }
        });

        /* ✅ On select product */
        productSelect.addEventListener('click',
        function() { // Changed from 'change' to 'click' for better UX with floating list
            const option = this.selectedOptions[
            0]; // Or rely on event target if options are divs, but here they are <option>

            // For single click on option in a size>1 select, 'change' triggers, but 'click' is safer for immediate selection
            if (!this.value) return;

            // Find the selected option element explicitly if needed, but 'this.value' works for select
            const selectedOption = this.options[this.selectedIndex];

            if (!selectedOption) return;

            const productId = selectedOption.value;
            const productName = selectedOption.dataset.name;
            const productCode = selectedOption.dataset.code;

            addProductToTable(productId, productName, productCode);

            // Reset search
            filterInput.value = '';

            // Hide list
            this.style.display = 'none';

            // Reset selection
            this.selectedIndex = -1;
        });

        /* 🔧 Your table logic placeholder */
        function addProductToTable(id, name, code) {
            console.log('Added product:', id, name, code);

            // TODO:
            // 1. Prevent duplicates
            // 2. Append row to purchase table
        }
    </script>
@endpush
