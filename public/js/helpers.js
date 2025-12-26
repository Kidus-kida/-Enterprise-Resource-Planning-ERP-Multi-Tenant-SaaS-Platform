/**
 * Helper functions for number formatting and calculations
 * Used by product.js and other modules
 */

// Read number from input field
function __read_number(input) {
    if (typeof input === 'string') {
        input = $(input);
    }

    var value = input.val();
    if (!value) return 0;

    // Use accounting.js to parse the number if available
    if (typeof accounting !== 'undefined') {
        return accounting.unformat(value, '.');
    }

    // Fallback: remove thousand separators and parse
    value = value.toString().replace(/,/g, '');
    return parseFloat(value) || 0;
}

// Write formatted number to input field
function __write_number(input, value) {
    if (typeof input === 'string') {
        input = $(input);
    }

    if (value === null || value === undefined || isNaN(value)) {
        value = 0;
    }

    // Format to 2 decimal places
    var formatted = parseFloat(value).toFixed(2);
    input.val(formatted);
}

// Add percentage to an amount
function __add_percent(amount, percent) {
    if (!amount) amount = 0;
    if (!percent) percent = 0;

    var result = parseFloat(amount) + (parseFloat(amount) * parseFloat(percent) / 100);
    return parseFloat(result.toFixed(2));
}

// Get principal amount from total (reverse percentage calculation)
function __get_principle(total, rate) {
    if (!total) total = 0;
    if (!rate) rate = 0;

    var principal = parseFloat(total) / (1 + parseFloat(rate) / 100);
    return parseFloat(principal.toFixed(2));
}

// Calculate percentage rate between cost and price
function __get_rate(cost, price) {
    if (!cost || cost == 0) return 0;
    if (!price) price = 0;

    var rate = ((parseFloat(price) - parseFloat(cost)) / parseFloat(cost)) * 100;
    return parseFloat(rate.toFixed(2));
}

// Format currency
function __currency_format(amount) {
    if (typeof accounting !== 'undefined') {
        return accounting.formatMoney(amount);
    }
    return parseFloat(amount).toFixed(2);
}

// Recursively convert currency in element
function __currency_convert_recursively(element) {
    element.find('.display_currency').each(function () {
        var amount = $(this).data('currency_value');
        if (amount !== undefined) {
            $(this).text(__currency_format(amount));
        }

    });
}

// Datatable AJAX callback to add global parameters
function __datatable_ajax_callback(data) {
    if (typeof data === 'undefined') {
        data = {};
    }
    // Add any global parameters needed for all datatables
    return data;
}

// Global variable for datepicker format (often used by datepickers in tables)
var datepicker_date_format = "mm/dd/yyyy";

// Disable submit button during AJAX
function __disable_submit_button(element) {
    if (element.length) {
        element.attr('disabled', true);
        var original_text = element.text();
        element.data('original-text', original_text);
        element.text('Please wait...');
    }
}

