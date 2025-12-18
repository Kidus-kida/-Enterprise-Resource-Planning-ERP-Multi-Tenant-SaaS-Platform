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
                        <a class="nav-link @if(empty(session('status.tab'))) active @endif" href="#variation"
                            data-bs-toggle="tab" role="tab" aria-controls="variation" aria-selected="true">
                            <i class="fa-solid fa-layer-group"></i> Variations
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link @if(session('status.tab') == 'variation_transfer') active @endif"
                            href="#variation_transfer" data-bs-toggle="tab" role="tab" aria-controls="variation_transfer"
                            aria-selected="false">
                            <i class="fa-solid fa-arrow-right-arrow-left"></i> Variation Transfer
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    <div class="tab-pane fade @if(empty(session('status.tab'))) show active @endif" id="variation"
                        role="tabpanel" aria-labelledby="variation-tab">
                        @include('products::variation.partials.variations')
                    </div>
                    <div class="tab-pane fade @if(session('status.tab') == 'variation_transfer') show active @endif"
                        id="variation_transfer" role="tabpanel" aria-labelledby="variation_transfer-tab">
                        @include('products::variation_transfer.index')
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade variation_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
@endsection


@push('page-scripts')
    @vite([
        "resources/js/datatables.js"
    ])
    <script type="module">
        const $ = window.jQuery;
        const Toastify = window.Toastify;

        // Ensure DataTables errors are shown in console for debugging
        if ($.fn.dataTable) {
            $.fn.dataTable.ext.errMode = 'throw';
        }

        // Variation Table Initialization
        window.variation_table = $('#variation_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("products.variations.index") }}',
                type: 'GET',
                error: function (xhr, error, code) {
                    console.log('DataTables AJAX error:', error, code, xhr.responseText);
                    if (typeof Toastify !== 'undefined') {
                        Toastify({ text: "Table load error. Please refresh.", className: "danger" }).showToast();
                    }
                }
            },
            columns: [
                { data: 'name', name: 'name', searchable: true, orderable: true },
                { data: 'values', name: 'values', searchable: true, orderable: false },
                { data: 'action', name: 'action', searchable: false, orderable: false }
            ]
        });

        // Add handler for the Refresh button
        $(document).on('click', '#refresh_variation_table', function() {
            if (window.variation_table) {
                window.variation_table.ajax.reload();
                Toastify({ text: "Table refreshed", className: "info" }).showToast();
            }
        });

        // Note: Form submission handlers are now embedded directly in 
        // variation/create.blade.php and variation/edit.blade.php 
        // to ensure they attach correctly when loaded via AJAX.

        // Delete Variation Handler (Remains here as it's on the main table buttons)
        $(document).on('click', 'button.delete_variation_button', function (e) {
            e.preventDefault();
            if (confirm("Are you sure you want to delete this variation?")) {
                var href = $(this).data('href');
                $.ajax({
                    method: 'DELETE',
                    url: href,
                    dataType: 'json',
                    data: { _token: "{{ csrf_token() }}" },
                    success: function (result) {
                        if (result.success === true) {
                            if (typeof Toastify !== 'undefined') {
                                Toastify({ text: result.msg, className: "success", }).showToast();
                            } else {
                                alert(result.msg);
                            }

                            if (window.variation_table) {
                                window.variation_table.ajax.reload();
                            } else {
                                $('#variation_table').DataTable().ajax.reload();
                            }
                        } else {
                            if (typeof Toastify !== 'undefined') {
                                Toastify({ text: result.msg, className: "danger", }).showToast();
                            } else {
                                alert(result.msg);
                            }
                        }
                    },
                    error: function (xhr) {
                        if (typeof Toastify !== 'undefined') {
                            Toastify({ text: "Error: " + xhr.statusText, className: "danger", }).showToast();
                        } else {
                            alert("Error: " + xhr.statusText);
                        }
                    }
                });
            }
        });

        // Legacy Transfer Code (Disabled)
        if ($('#form_date_range').length == 1) {

            $('#filter_location_from').change(function () {
                let check_store_not = null;
                $.ajax({
                    method: 'get',
                    url: '/products/variation-transfer/get-store/' + $(this).val(),
                    data: { check_store_not: check_store_not },
                    success: function (result) {

                        $('#filter_from_store').empty();
                        $('#filter_from_store').append(`<option value="">Please Select</option>`);
                        $.each(result, function (i, location) {
                            $('#filter_from_store').append(`<option value= "` + location.id + `">` + location.name + `</option>`);
                        });
                    },
                });
            });
            $('#filter_location_to').change(function () {
                let check_store_not = null;
                $.ajax({
                    method: 'get',
                    url: '/products/variation-transfer/get-store/' + $(this).val(),
                    data: { check_store_not: check_store_not },
                    success: function (result) {

                        $('#filter_to_store').empty();
                        $('#filter_to_store').append(`<option value="">Please Select</option>`);
                        $.each(result, function (i, location) {
                            $('#filter_to_store').append(`<option value= "` + location.id + `">` + location.name + `</option>`);
                        });
                    },
                });
            });

            $('#filter_category_id, #filter_sub_category_id').change(function () {
                var this_id = $(this).attr('id');
                var cat = $('#filter_category_id').val();
                var sub_cat = $('#filter_sub_category_id').val();
                $.ajax({
                    method: 'POST',
                    url: '/products/get_sub_categories',
                    dataType: 'html',
                    data: { cat_id: cat, _token: "{{ csrf_token() }}" },
                    success: function (result) {
                        if (result) {
                            console.log(this_id);
                            if (this_id !== 'filter_sub_category_id') {
                                $('#filter_sub_category_id').html(result);
                            }
                        }
                    },
                });
                $.ajax({
                    method: 'GET',
                    url: '/products/variation-transfer/get-variation-by-category',
                    dataType: 'html',
                    data: { cat_id: cat, sub_cat_id: sub_cat },
                    success: function (result) {
                        if (result) {
                            $('#filter_from_variation_id').html(result);
                            $('#filter_to_variation_id').html(result);
                        }
                    },
                });
            });

            $(document).ready(function () {
                variation_transfer_table = $('#variation_transfer_table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: '/products/variation-transfer',
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

            $(document).on('click', 'a.delete-variation-transfer', function (e) {
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

                        $.ajax({
                            method: 'DELETE',
                            url: href,
                            dataType: 'json',
                            data: { _token: "{{ csrf_token() }}" },
                            success: function (result) {
                                if (result.success === true || result.success === 1) {
                                    Toastify({ text: result.msg, className: "success", }).showToast();
                                    variation_transfer_table.ajax.reload();
                                } else {
                                    Toastify({ text: result.msg, className: "danger", }).showToast();
                                }
                            },
                            error: function (xhr) {
                                Toastify({ text: "Error: " + xhr.statusText, className: "danger", }).showToast();
                            }
                        });
                    }
                });
            });
        }
    </script>
@endpush