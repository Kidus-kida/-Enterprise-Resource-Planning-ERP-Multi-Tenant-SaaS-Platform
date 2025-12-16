/**
 * Purchase Creation JavaScript
 * Handles product selection, calculations, and payment management
 */

$(document).ready(function () {

    // Initialize row count
    let row_count = 0;

    //===========================================
    // PRODUCT SELECTION
    //===========================================

    /**
     * Product autocomplete using the filter input
     */
    let debounceTimeout;
    $('#product-filter').on('input', function () {
        const query = $(this).val().toLowerCase();
        const productSelect = $('#product-select');

        if (!query) {
            productSelect.hide();
            return;
        }

        clearTimeout(debounceTimeout);
        debounceTimeout = setTimeout(() => {
            let hasResults = false;
            $('#product-select option').each(function () {
                const text = $(this).text().toLowerCase();
                const match = text.includes(query);
                $(this).toggle(match);
                if (match) hasResults = true;
            });

            productSelect.toggle(hasResults);
        }, 300);
    });

    /**
     * Handle product selection
     */
    $('#product-select').on('click', function () {
        const selectedOption = $(this).find('option:selected');
        if (!selectedOption.val()) return;

        const productId = selectedOption.val();
        const variationId = selectedOption.data('variation-id') || 0;

        addProductRow(productId, variationId);

        // Reset search
        $('#product-filter').val('');
        $(this).hide().prop('selectedIndex', -1);
    });

    /**
     * Add product row via AJAX
     */
    function addProductRow(productId, variationId = 0) {
        // Check for duplicates
        let duplicate = false;
        $('.hidden_variation_id').each(function () {
            if (parseInt($(this).val()) === parseInt(variationId)) {
                duplicate = true;
                return false;
            }
        });

        if (duplicate) {
            alert('This product is already added!');
            return;
        }

        const locationId = $('[name="location_id"]').val();
        if (!locationId) {
            alert('Please select a business location first!');
            return;
        }

        $.ajax({
            method: 'POST',
            url: '/purchases/get_purchase_entry_row',
            dataType: 'html',
            data: {
                product_id: productId,
                variation_id: variationId,
                row_count: row_count,
                location_id: locationId,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function (result) {
                $('#product-table-body').append(result);
                row_count++;

                // Initialize calculations for the new row
                const newRow = $('#product-table-body tr:last');
                updateRowCalculations(newRow);
                updateTableTotal();
                updateGrandTotal();
                updateSerialNumbers();
            },
            error: function (xhr) {
                console.error('Error adding product:', xhr);
                alert('Failed to add product. Please try again.');
            }
        });
    }

    //===========================================
    // CALCULATION HELPERS
    //===========================================

    /**
     * Read number from input (handles formatted numbers)
     */
    function readNumber(element) {
        let val = $(element).val();
        if (!val) return 0;
        // Remove thousand separators and parse
        val = val.toString().replace(/,/g, '');
        return parseFloat(val) || 0;
    }

    /**
     * Write number to input
     */
    function writeNumber(element, value) {
        $(element).val(parseFloat(value).toFixed(2));
    }

    /**
     * Format currency display
     */
    function formatCurrency(value) {
        return parseFloat(value).toFixed(2);
    }

    /**
     * Calculate percentage amount
     */
    function calculatePercentage(rate, base) {
        return (parseFloat(rate) / 100) * parseFloat(base);
    }

    /**
     * Get principle from amount with percentage
     */
    function getPrinciple(amount, rate) {
        return parseFloat(amount) / (1 + parseFloat(rate) / 100);
    }

    //===========================================
    // ROW-LEVEL CALCULATIONS
    //===========================================

    /**
     * Update all calculations for a row
     */
    function updateRowCalculations(row) {
        const quantity = readNumber(row.find('.purchase_quantity'));
        const unitCostBeforeDiscount = readNumber(row.find('.purchase_unit_cost_without_discount'));
        const discountPercent = readNumber(row.find('.inline_discounts'));

        // Calculate unit cost after discount but before tax
        const discountAmount = calculatePercentage(discountPercent, unitCostBeforeDiscount);
        const unitCostBeforeTax = unitCostBeforeDiscount - discountAmount;
        writeNumber(row.find('.purchase_unit_cost'), unitCostBeforeTax);

        // Get tax rate
        const taxRate = parseFloat(row.find('.purchase_line_tax_id option:selected').data('tax_amount')) || 0;
        const taxAmount = calculatePercentage(taxRate, unitCostBeforeTax);

        // Calculate unit cost after tax
        const unitCostAfterTax = unitCostBeforeTax + taxAmount;
        writeNumber(row.find('.purchase_unit_cost_after_tax'), unitCostAfterTax);

        // Calculate subtotals
        const subtotalBeforeTax = quantity * unitCostBeforeTax;
        const subtotalAfterTax = quantity * unitCostAfterTax;

        // Update displays
        row.find('.row_subtotal_before_tax').text(formatCurrency(subtotalBeforeTax));
        row.find('.row_subtotal_before_tax_hidden').val(subtotalBeforeTax);

        row.find('.row_subtotal_after_tax').text(formatCurrency(subtotalAfterTax));
        row.find('.row_subtotal_after_tax_hidden').val(subtotalAfterTax);

        row.find('.purchase_product_unit_tax_text').text(formatCurrency(taxAmount));
        row.find('.purchase_product_unit_tax').val(taxAmount);

        // Update profit margin if selling price is set
        updateProfitMargin(row);
    }

    /**
     * Update profit margin percentage
     */
    function updateProfitMargin(row) {
        const unitCostAfterTax = readNumber(row.find('.purchase_unit_cost_after_tax'));
        const sellingPrice = readNumber(row.find('.default_sell_price'));

        if (unitCostAfterTax > 0) {
            const profitPercent = ((sellingPrice - unitCostAfterTax) / unitCostAfterTax) * 100;
            writeNumber(row.find('.profit_percent'), profitPercent);
        }
    }

    //===========================================
    // TABLE-LEVEL CALCULATIONS
    //===========================================

    /**
     * Update table total (sum of all rows)
     */
    function updateTableTotal() {
        let total = 0;
        $('.row_subtotal_after_tax_hidden').each(function () {
            total += parseFloat($(this).val()) || 0;
        });

        $('#display-grand-total').text(formatCurrency(total));
        $('[name="total_before_tax"]').val(total);

        return total;
    }

    /**
     * Update grand total (with shipping and adjustments)
     */
    function updateGrandTotal() {
        const subtotal = updateTableTotal();
        const discountAmount = readNumber($('[name="discount_amount"]')) || 0;
        const shippingCharges = readNumber($('[name="shipping_charges"]')) || 0;

        const grandTotal = subtotal - discountAmount + shippingCharges;

        $('#display-grand-total').text(formatCurrency(grandTotal));
        $('[name="final_total"]').val(grandTotal);

        // Update payment due
        updatePaymentDue(grandTotal);

        return grandTotal;
    }

    /**
     * Update serial numbers for table rows
     */
    function updateSerialNumbers() {
        $('#product-table-body tr.purchase_entry_row').each(function (index) {
            $(this).find('.sr_number').text(index + 1);
        });
    }

    //===========================================
    // EVENT HANDLERS
    //===========================================

    // Quantity change
    $(document).on('change', '.purchase_quantity', function () {
        const row = $(this).closest('tr');
        updateRowCalculations(row);
        updateTableTotal();
        updateGrandTotal();
    });

    // Unit cost before discount change
    $(document).on('change', '.purchase_unit_cost_without_discount', function () {
        const row = $(this).closest('tr');
        updateRowCalculations(row);
        updateTableTotal();
        updateGrandTotal();
    });

    // Discount percent change
    $(document).on('change', '.inline_discounts', function () {
        const row = $(this).closest('tr');
        updateRowCalculations(row);
        updateTableTotal();
        updateGrandTotal();
    });

    // Tax selection change
    $(document).on('change', '.purchase_line_tax_id', function () {
        const row = $(this).closest('tr');
        updateRowCalculations(row);
        updateTableTotal();
        updateGrandTotal();
    });

    // Unit cost after tax change (reverse calculation)
    $(document).on('change', '.purchase_unit_cost_after_tax', function () {
        const row = $(this).closest('tr');
        const quantity = readNumber(row.find('.purchase_quantity'));
        const unitCostAfterTax = readNumber($(this));

        // Get tax rate  
        const taxRate = parseFloat(row.find('.purchase_line_tax_id option:selected').data('tax_amount')) || 0;

        // Reverse calculate unit cost before tax
        const unitCostBeforeTax = getPrinciple(unitCostAfterTax, taxRate);
        writeNumber(row.find('.purchase_unit_cost'), unitCostBeforeTax);

        // Calculate tax amount
        const taxAmount = unitCostAfterTax - unitCostBeforeTax;

        // Update discount calculation
        const discountPercent = readNumber(row.find('.inline_discounts'));
        const unitCostBeforeDiscount = getPrinciple(unitCostBeforeTax, discountPercent);
        writeNumber(row.find('.purchase_unit_cost_without_discount'), unitCostBeforeDiscount);

        // Calculate subtotals
        const subtotalBeforeTax = quantity * unitCostBeforeTax;
        const subtotalAfterTax = quantity * unitCostAfterTax;

        // Update displays
        row.find('.row_subtotal_before_tax').text(formatCurrency(subtotalBeforeTax));
        row.find('.row_subtotal_before_tax_hidden').val(subtotalBeforeTax);

        row.find('.row_subtotal_after_tax').text(formatCurrency(subtotalAfterTax));
        row.find('.row_subtotal_after_tax_hidden').val(subtotalAfterTax);

        row.find('.purchase_product_unit_tax_text').text(formatCurrency(taxAmount));
        row.find('.purchase_product_unit_tax').val(taxAmount);

        updateProfitMargin(row);
        updateTableTotal();
        updateGrandTotal();
    });

    // Profit margin change
    $(document).on('change', '.profit_percent', function () {
        const row = $(this).closest('tr');
        const profitPercent = readNumber($(this));
        const unitCostAfterTax = readNumber(row.find('.purchase_unit_cost_after_tax'));

        const sellingPrice = unitCostAfterTax + calculatePercentage(profitPercent, unitCostAfterTax);
        writeNumber(row.find('.default_sell_price'), sellingPrice);
    });

    // Selling price change
    $(document).on('change', '.default_sell_price', function () {
        const row = $(this).closest('tr');
        updateProfitMargin(row);
    });

    // Remove row
    $(document).on('click', '.remove_purchase_entry_row', function () {
        if (confirm('Are you sure you want to remove this item?')) {
            $(this).closest('tr').next('.lot_number_row').remove();
            $(this).closest('tr').remove();
            updateTableTotal();
            updateGrandTotal();
            updateSerialNumbers();
        }
    });

    //===========================================
    // PAYMENT MANAGEMENT
    //===========================================

    /**
     * Add new payment row
     */
    $(document).on('click', '#add-payment-row', function () {
        const currentIndex = parseInt($('.payment_row_index').last().val()) || 0;
        const newIndex = currentIndex + 1;
        const locationId = $('[name="location_id"]').val();

        $.ajax({
            method: 'POST',
            url: '/purchases/get_payment_row',
            data: {
                row_index: newIndex,
                location_id: locationId,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            dataType: 'html',
            success: function (result) {
                $('#payment_rows_div').append(result);

                // Initialize cheque date picker for the new row
                initChequeDatePicker(newIndex);

                // Auto-fill payment amount with remaining due
                const grandTotal = parseFloat($('[name="final_total"]').val()) || 0;
                let totalPayment = 0;

                $('.payment-amount').not(':last').each(function () {
                    totalPayment += readNumber($(this));
                });

                const remainingDue = grandTotal - totalPayment;
                $('.payment-amount').last().val(Math.max(0, remainingDue).toFixed(2));

                updatePaymentDue();
            },
            error: function (xhr) {
                console.error('Error adding payment row:', xhr);
                alert('Failed to add payment row. Please try again.');
            }
        });
    });

    /**
     * Remove payment row
     */
    $(document).on('click', '.remove-payment-row', function () {
        const paymentRows = $('.payment-row');
        if (paymentRows.length > 1) {
            $(this).closest('.payment-row').remove();
            updatePaymentDue();
        } else {
            alert('At least one payment method is required.');
        }
    });

    /**
     * Handle payment method change - show/hide relevant fields
     */
    /**
     * Handle payment method change - show/hide relevant fields
     */
    $(document).on('change', '.payment_types_dropdown', function () {
        const row = $(this).closest('.payment-row');
        const method = $(this).val();
        const rowIndex = row.find('.payment_row_index').val();

        // Hide all conditional fields first
        row.find('.account_module').hide();
        row.find('.bank_transfer_fields').hide();
        row.find('.cheque_payment_details_only').addClass('d-none'); // Hide cheque list

        // Show fields based on payment method
        if (method === 'cash' || method === 'bank_transfer' || method === 'cheque' || method === 'card') {
            row.find('.account_module').show();
            // loadPaymentAccounts(rowIndex, method); 
        }

        if (method === 'bank_transfer' || method === 'cheque') {
            row.find('.bank_transfer_fields').show();
        }

        // Special handling for cheque
        if (method === 'cheque') {
            row.find('.cheque_payment_details_only').removeClass('d-none');

            // Trigger cheque list fetch if date range is set
            if ($('#transaction_date_range_cheque_deposit_' + rowIndex).val()) {
                getChequesList(rowIndex);
            }
        }
    });

    function initChequeDatePicker(rowIndex) {
        const pickerId = '#transaction_date_range_cheque_deposit_' + rowIndex;
        if ($(pickerId).length) {
            $(pickerId).daterangepicker({
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                },
                startDate: moment().subtract(29, 'days'),
                endDate: moment()
            }, function (start, end) {
                getChequesList(rowIndex, start.format('YYYY-MM-DD'), end.format('YYYY-MM-DD'));
            });
        }
    }

    function getChequesList(rowIndex, start_date = null, end_date = null) {
        const pickerId = '#transaction_date_range_cheque_deposit_' + rowIndex;

        if (!start_date) {
            start_date = $(pickerId).data('daterangepicker').startDate.format('YYYY-MM-DD');
        }
        if (!end_date) {
            end_date = $(pickerId).data('daterangepicker').endDate.format('YYYY-MM-DD');
        }

        $.ajax({
            method: 'GET',
            url: '/purchases/cheque-list',
            data: { start_date: start_date, end_date: end_date },
            dataType: 'html',
            success: function (result) {
                $('#cheque_list_table_' + rowIndex + ' tbody').empty().append(result);
            },
            error: function (xhr) {
                console.error('Error fetching cheque list:', xhr);
            }
        });
    }

    // Initialize for existing rows on page load
    $(document).ready(function () {
        $('.cheque_date_range').each(function () {
            const rowIndex = $(this).data('row_index');
            initChequeDatePicker(rowIndex);
        });
    });

    /**
     * Load payment accounts based on payment method
     */
    function loadPaymentAccounts(rowIndex, paymentMethod) {
        const accountDropdown = $('#account_' + rowIndex);

        // This would normally make an AJAX call to get accounts for the payment method
        // For now, we'll just show the dropdown
        // In production, you'd call an endpoint like:
        // $.ajax({
        //     url: '/get-accounts-for-payment-method',
        //     data: { method: paymentMethod },
        //     success: function(accounts) {
        //         accountDropdown.html('<option value="">Select Account</option>');
        //        Object.keys(accounts).forEach(id => {
        //             accountDropdown.append('<option value="' + id + '">' + accounts[id] + '</option>');
        //         });
        //     }
        // });
    }

    /**
     * Calculate and display payment due
     */
    function updatePaymentDue(grandTotal) {
        grandTotal = grandTotal || parseFloat($('[name="final_total"]').val()) || 0;

        let totalPayment = 0;
        $('.payment-amount').each(function () {
            totalPayment += readNumber($(this));
        });

        const paymentDue = grandTotal - totalPayment;
        $('#payment_due').text(formatCurrency(Math.max(0, paymentDue)));
    }

    // Payment amount change
    $(document).on('change', '.payment-amount', function () {
        updatePaymentDue();
    });

    // Update payment input names to match backend expectations (payment not payments)
    function fixPaymentInputNames() {
        $('.payment-row').each(function (index) {
            $(this).find('[name^="payments"]').each(function () {
                const name = $(this).attr('name');
                const newName = name.replace('payments', 'payment');
                $(this).attr('name', newName);
            });
        });
    }

    // Fix payment names before submit
    $('form').on('submit', function () {
        fixPaymentInputNames();
    });

    //===========================================
    // INITIALIZATION
    //===========================================

    // Remove example rows on page load
    $('#product-table-body tr').not('#no-items-row').remove();
    $('#no-items-row').show();

    // Hide no-items row when products are added
    $(document).on('DOMNodeInserted', '#product-table-body', function () {
        if ($('#product-table-body tr.purchase_entry_row').length > 0) {
            $('#no-items-row').hide();
        } else {
            $('#no-items-row').show();
        }
    });

    // Initialize grand total on page load
    updateGrandTotal();

    console.log('Purchase module initialized');
});
