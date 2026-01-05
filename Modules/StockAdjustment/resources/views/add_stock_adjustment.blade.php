@extends('layouts.app')

@section('page-content')
<div class="content container-fluid">

    <!-- Page Header -->
    <x-breadcrumb class="col">
        <x-slot name="title">Stock-Adjustment</x-slot>
        <ul class="breadcrumb">
            <li class="breadcrumb-item active">All stock adjustments</li>
        </ul>
        <x-slot name="right">
            <div class="col-auto float-end ms-auto">
                <a href="{{ route('stock_adjustment.index') }}" class="btn btn-secondary">
                    <i class="fa-solid fa-arrow-left"></i> Back
                </a>
            </div>
        </x-slot>
    </x-breadcrumb>
    <!-- /Page Header -->

    <div class="row">
        <div class="col-md-12">
            <div class="card p-3">
                <h5 class="mb-3">Add Stock Adjustment</h5>

                <form action="{{ route('stock_adjustment.store') }}" method="POST" id="stock_adjustment_form">
                    @csrf

                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label>Business Location:*</label>
                            <select name="location_id" id="location_id" class="form-control select2" required>
                                <option value="">Select Location</option>
                                @foreach($business_locations ?? [] as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label>Store:*</label>
                            <select name="store_id" id="store_id" class="form-control select2" required>
                                <option value="">Select Store</option>
                                @foreach($stores ?? [] as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label>Reference No:</label>
                            <input type="text" name="ref_no" class="form-control" value="{{ $ref_no }}" readonly>
                        </div>

                        <div class="col-md-3">
                            <label>Date:*</label>
                            <input type="datetime-local" name="transaction_date" id="transaction_date" class="form-control" value="{{ now()->format('Y-m-d\TH:i') }}" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label>Adjustment Type:*</label>
                            <select name="adjustment_type" id="adjustment_type" class="form-control select2" required>
                                <option value="">Please Select</option>
                                <option value="normal">Normal</option>
                                <option value="abnormal">Abnormal</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label>Stock Adjustment Type:*</label>
                            <select name="stock_adjustment_type" id="stock_adjustment_type" class="form-control select2" required>
                                <option value="">Please Select</option>
                                <option value="increase">Increase</option>
                                <option value="decrease">Decrease</option>
                            </select>
                        </div>
                    </div>

                    <!-- Search Products -->
                    <div class="row mb-3">
                        <div class="col-sm-8 offset-sm-2">
                            <div class="form-group">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fa fa-search"></i>
                                    </span>
                                    <input type="text" id="search_product_for_srock_adjustment" class="form-control" placeholder="Search products for stock adjustment">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Products Table -->
                    <div class="table-responsive mb-3">
                        <input type="hidden" id="product_row_index" value="0">
                        <input type="hidden" id="total_amount" name="final_total" value="0">
                        <table class="table table-bordered table-striped table-condensed" id="stock_adjustment_product_table">
                            <thead>
                                <tr>
                                    <th class="col-sm-3 text-center">Product</th>
                                    <th class="col-sm-2 text-center">Current Stock</th>
                                    <th class="col-sm-2 text-center">Quantity</th>
                                    <th class="col-sm-2 text-center">Unit Price</th>
                                    <th class="col-sm-2 text-center">Subtotal</th>
                                    <th class="col-sm-1 text-center"><i class="fa fa-trash" aria-hidden="true"></i></th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                            <tfoot>
                                <tr class="text-center">
                                    <td colspan="3"></td>
                                    <td><div class="float-end"><b>Total Amount:</b> <span id="total_adjustment">0.00</span></div></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label>Total amount recovered:</label>
                            <input type="number" name="total_amount_recovered" class="form-control" value="0">
                        </div>
                        <div class="col-md-6">
                            <label>Reason:</label>
                            <textarea name="additional_notes" class="form-control" placeholder="Reason" rows="3"></textarea>
                        </div>
                    </div>

                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">Save</button>
                        <a href="{{ route('stock_adjustment.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>

                </form>
            </div>
        </div>
    </div>

</div>
@endsection

@push('page-scripts')
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

<script type="text/javascript">
    $(document).ready(function() {
        if ($('#search_product_for_srock_adjustment').length > 0) {
            $('#search_product_for_srock_adjustment').autocomplete({
                source: function(request, response) {
                    $.getJSON(
                        '/products/list-sa',
                        { location_id: $('#location_id').val(), store_id: $('#store_id').val(), term: request.term },
                        response
                    );
                },
                minLength: 2,
                response: function(event, ui) {
                    if (ui.content.length == 1) {
                        ui.item = ui.content[0];
                        if (ui.item.qty_available > 0 && ui.item.enable_stock == 1) {
                            $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
                            $(this).autocomplete('close');
                        }
                    } else if (ui.content.length == 0) {
                        swal("No matching product found!");
                    }
                },
                select: function(event, ui) {
                    if (ui.item.qty_available > 0) {
                        $(this).val(null);
                        stock_adjustment_product_row(ui.item.variation_id);
                    } else {
                        alert("Out of stock");
                    }
                },
            }).autocomplete('instance')._renderItem = function(ul, item) {
                var string = '<div>' + item.name;
                if (item.type == 'variable') {
                    string += '-' + item.variation;
                }
                string += ' (' + item.sub_sku + ') </div>';
                return $('<li>').append(string).appendTo(ul);
            };
        }

        $('select#location_id').change(function() {
            if ($(this).val()) {
                $('#search_product_for_srock_adjustment').removeAttr('disabled');
                
                // Update store dropdown based on location
                $.ajax({
                    method: 'get',
                    url: "{{ url('products/variation-transfer/get-store') }}/" + $(this).val(),
                    data: { permission: 'purchase' },
                    success: function (result) {
                        $('#store_id').empty();
                        $('#store_id').append('<option value="">Select Store</option>');
                        $.each(result, function (i, store) {
                            $('#store_id').append(`<option value="${store.id}">${store.name}</option>`);
                        });
                    },
                });
            } else {
                $('#search_product_for_srock_adjustment').attr('disabled', 'disabled');
            }
            $('table#stock_adjustment_product_table tbody').html('');
            $('#product_row_index').val(0);
            update_table_total();
        });

        $(document).on('change', 'input.product_quantity', function() {
            update_table_row($(this).closest('tr'));
        });
        $(document).on('change', 'input.product_unit_price', function() {
            update_table_row($(this).closest('tr'));
        });

        $(document).on('click', '.remove_product_row', function() {
            if(confirm("Are you sure?")) {
                $(this).closest('tr').remove();
                update_table_total();
            }
        });
        
        // Trigger location change on load if value exists
        if($('#location_id').val()){
            $('#location_id').trigger('change');
        }
    });

    function stock_adjustment_product_row(variation_id) {
        var row_index = parseInt($('#product_row_index').val());
        var location_id = $('select#location_id').val();
        $.ajax({
            method: 'POST',
            url: '{{ route("stock_adjustment.get_product_row") }}',
            data: { row_index: row_index, variation_id: variation_id, location_id: location_id },
            dataType: 'html',
            success: function(result) {
                $('table#stock_adjustment_product_table tbody').append(result);
                update_table_total();
                $('#product_row_index').val(row_index + 1);
            },
        });
    }

    function update_table_total() {
        var table_total = 0;
        $('table#stock_adjustment_product_table tbody tr').each(function() {
            var this_total = parseFloat($(this).find('input.product_line_total').val().replace(/,/g, ''));
            if (this_total) {
                table_total += this_total;
            }
        });
        $('input#total_amount').val(table_total);
        $('span#total_adjustment').text(table_total.toLocaleString());
    }

    function update_table_row(tr) {
        var quantity = parseFloat(tr.find('input.product_quantity').val().replace(/,/g, ''));
        var unit_price = parseFloat(tr.find('input.product_unit_price').val().replace(/,/g, ''));
        var row_total = 0;
        if (quantity && unit_price) {
            row_total = quantity * unit_price;
        }
        tr.find('input.product_line_total').val(row_total.toLocaleString());
        update_table_total();
    }
</script>
@endpush
