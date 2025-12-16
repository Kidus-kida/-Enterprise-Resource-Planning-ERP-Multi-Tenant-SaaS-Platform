@extends('layouts.app')
@section('title', 'Variations')

@section('page-content')
<div class="content container-fluid">
    <!-- Page Header -->
    <x-breadcrumb class="col">
        <x-slot name="title">Variations</x-slot>
        <ul class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('dashboard') }}">Dashboard</a>
            </li>
            <li class="breadcrumb-item active">
                Variations
            </li>
        </ul>
        <x-slot name="right">
            <!-- New add button is handled inside the tab content partials now, or we can move it here if generic -->
        </x-slot>
    </x-breadcrumb>
    <!-- /Page Header -->

    <div class="card mb-3">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" id="variation-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link @if(empty(session('status.tab'))) active @endif" href="#variation" data-bs-toggle="tab" role="tab" aria-controls="variation" aria-selected="true">
                        <i class="fa-solid fa-layer-group"></i> Variations
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link @if(session('status.tab') == 'variation_transfer') active @endif" href="#variation_transfer" data-bs-toggle="tab" role="tab" aria-controls="variation_transfer" aria-selected="false">
                        <i class="fa-solid fa-arrow-right-arrow-left"></i> Variation Transfer
                    </a>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content">
                <div class="tab-pane fade @if(empty(session('status.tab'))) show active @endif" id="variation" role="tabpanel" aria-labelledby="variation-tab">
                    @include('products::variation.partials.variations')
                </div>
                <div class="tab-pane fade @if(session('status.tab') == 'variation_transfer') show active @endif" id="variation_transfer" role="tabpanel" aria-labelledby="variation_transfer-tab">
                    @include('products::variation_transfer.index')
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade variation_modal" tabindex="-1" role="dialog" 
    aria-labelledby="gridSystemModalLabel">
</div>
@endsection


@push('page-scripts')
    @vite([
        "resources/js/datatables.js"
    ])
    <script type="module">
    // Ensure jQuery is loaded before running
    document.addEventListener('DOMContentLoaded', function() {
    var body = document.getElementsByTagName("body")[0];
    body.className += " sidebar-collapse";

    // Variation Table Initialization (Fixing missing DataTable)
    var variation_table = $('#variation_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{action([\Modules\Products\Http\Controllers\VariationTemplateController::class, 'index'])}}',
        columnDefs: [ {
            "targets": 2,
            "orderable": false,
            "searchable": false
        } ],
        columns: [
            { data: 'name', name: 'name'},
            { data: 'values', name: 'values'},
            { data: 'action', name: 'action'}
        ]
    });

    // Modal handler is now handled globally via data-ajax-modal


    // Form Submission Handler for Modals
    $(document).on('submit', 'form#variation_add_form', function(e) {
        e.preventDefault();
        $(this).find('button[type="submit"]').attr('disabled', true);
        var data = $(this).serialize();

        $.ajax({
            method: 'POST',
            url: $(this).attr('action'),
            dataType: 'json',
            data: data,
            success: function(result) {
                if (result.success === true) {
                    $('#variation_add_form').closest('.modal').modal('hide');
                    Toastify({ text: result.msg, className: "success", }).showToast();
                    variation_table.ajax.reload();
                } else {
                    Toastify({ text: result.msg, className: "danger", }).showToast();
                }
            }
        });
    });

    // Form Submission Handler for Edit Modal
    $(document).on('submit', 'form#variation_edit_form', function(e) {
        e.preventDefault();
        $(this).find('button[type="submit"]').attr('disabled', true);
        var data = $(this).serialize();

        $.ajax({
            method: 'POST',
            url: $(this).attr('action'),
            dataType: 'json',
            data: data,
            success: function(result) {
                if (result.success === true) {
                    $('#variation_edit_form').closest('.modal').modal('hide');
                    Toastify({ text: result.msg, className: "success", }).showToast();
                    variation_table.ajax.reload();
                } else {
                    Toastify({ text: result.msg, className: "danger", }).showToast();
                }
            }
        });
    });

    // Add new variation value input field
    $(document).on('click', '#add_variation_values', function() {
        var html = '<div class="row mb-3">' +
                    '<div class="col-sm-7 offset-sm-3">' +
                        '<input type="text" name="variation_values[]" class="form-control" required placeholder="Value">' +
                    '</div>' +
                   '</div>';
        $('#variation_values').append(html);
    });


    // Delete Variation Handler (Native Confirm + AJAX)
    $(document).on('click', 'button.delete_variation_button', function(e) {
        e.preventDefault();
        if (confirm("Are you sure you want to delete this variation?")) {
            var href = $(this).data('href');
            var data = $(this).serialize();

            $.ajax({
                method: 'DELETE',
                url: href,
                dataType: 'json',
                data: data,
                success: function(result) {
                    if (result.success === true) {
                        Toastify({ text: result.msg, className: "success", }).showToast();
                        variation_table.ajax.reload();
                    } else {
                        Toastify({ text: result.msg, className: "danger", }).showToast();
                    }
                }
            });
        }
    });

    });

    // Legacy Transfer Code (Disabled)
    /*
    if ($('#form_date_range').length == 1) {

$('#from_location').change(function(){
    let check_store_not = null;
    $.ajax({
        method: 'get',
        url: '/stock-transfer/get_transfer_store_id/'+$(this).val(),
        data: { check_store_not: check_store_not},
        success: function(result) {
            
            $('#filter_from_store').empty();
            $('#filter_from_store').append(`<option value="">Please Select</option>`);
            $.each(result, function(i, location) {
                $('#filter_from_store').append(`<option value= "`+location.id+`">`+location.name+`</option>`);
            });
        },
    });
});
$('#to_location').change(function(){
    let check_store_not = null;
    $.ajax({
        method: 'get',
        url: '/stock-transfer/get_transfer_store_id/'+$(this).val(),
        data: { check_store_not: check_store_not},
        success: function(result) {
            
            $('#filter_to_store').empty();
            $('#filter_to_store').append(`<option value="">Please Select</option>`);
            $.each(result, function(i, location) {
                $('#filter_to_store').append(`<option value= "`+location.id+`">`+location.name+`</option>`);
            });
        },
    });
});

$('#filter_category_id, #filter_sub_category_id').change(function(){
    var this_id = $(this).attr('id');
    var cat = $('#filter_category_id').val();
    var sub_cat = $('#filter_sub_category_id').val();
    $.ajax({
        method: 'POST',
        url: '/products/get_sub_categories',
        dataType: 'html',
        data: { cat_id: cat },
        success: function(result) {
        if (result) {
            console.log(this_id);
            if(this_id !== 'filter_sub_category_id'){
                $('#filter_sub_category_id').html(result);
            }
        }
        },
    });
    $.ajax({
        method: 'GET',
        url: '/variation-transfer/get-variation-by-category',
        dataType: 'html',
        data: { cat_id: cat , sub_cat_id: sub_cat },
        success: function(result) {
        if (result) {
            $('#from_variation_id').html(result);
            $('#to_variation_id').html(result);
        }
        },
    });
});

$(document).ready(function () {
    variation_transfer_table = $('#variation_transfer_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '/variation-transfer',
            data: function (d) {
                if ($('#filter_location_from').length) {
                    d.from_location = $('#filter_location_from').val();
                }
                if ($('#filter_location_to').length) {
                    d.to_location = $('#filter_location_to').val();
                }
                if ($('#filter_from_store').length) {
                    d.from_store = $('#filter_from_store').val();
                }
                if ($('#filter_to_store').length) {
                    d.to_store = $('#filter_to_store').val();
                }
                if ($('#filter_category_id').length) {
                    d.category_id = $('#filter_category_id').val();
                }
                if ($('#filter_sub_category_id').length) {
                    d.sub_category_id = $('#filter_sub_category_id').val();
                }
                if ($('#filter_from_variation_id').length) {
                    d.from_variation_id = $('#filter_from_variation_id').val();
                }
                if ($('#filter_to_variation_id').length) {
                    d.to_variation_id = $('#filter_to_variation_id').val();
                }

                var start = '';
                var end = '';
                if ($('#form_date_range').val()) {
                    start = $('input#form_date_range')
                        .data('daterangepicker')
                        .startDate.format('YYYY-MM-DD');
                    end = $('input#form_date_range')
                        .data('daterangepicker')
                        .endDate.format('YYYY-MM-DD');
                }
                d.start_date = start;
                d.end_date = end;
            },
        },
        aaSorting: [[1, 'desc']],
        columns: [
            { data: 'action', name: 'action', orderable: false, searchable: false },
            { data: 'date', name: 'date' },
            { data: 'lf_name', name: 'lf.name' },
            { data: 'lt_name', name: 'lt.name' },
            { data: 'sf_name', name: 'sf.name' },
            { data: 'st_name', name: 'st.name' },
            { data: 'category_name', name: 'categories.name' },
            { data: 'sub_category_name', name: 'sub_category.name' },
            { data: 'fp_name', name: 'fp.name' },
            { data: 'tp_name', name: 'tp.name' },
            { data: 'qty', name: 'qty' },
            { data: 'unit_cost', name: 'unit_cost' },
            { data: 'total_cost', name: 'total_cost' },
            { data: 'added_by', name: 'users.username' },
           
        ],
        fnDrawCallback: function (oSettings) {
            __currency_convert_recursively($('#variation_transfer_table'));
        }
    });

    $('#form_date_range, #filter_location_from, #filter_location_to, #filter_from_store, #filter_to_store, #filter_category_id, #filter_sub_category_id, #filter_from_variation_id, #filter_to_variation_id').change(function () {
        variation_transfer_table.ajax.reload();
    })
});

    $(document).on('click', 'a.delete-variation-transfer', function(e) {
        e.preventDefault();
        swal({
            title: 'Are you sure?',
            text: 'This variation transfer will be deleted.',
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        }).then(willDelete => {
            if (willDelete) {
                var href = $(this).data('href');
                var data = $(this).serialize();

                $.ajax({
                    method: 'DELETE',
                    url: href,
                    dataType: 'json',
                    data: data,
                    success: function(result) {
                        if (result.success === 1) {
                            toastr.success(result.msg);
                            variation_transfer_table.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    },
                });
            }
        });
    });
    });
    */
    </script>
@endpush
