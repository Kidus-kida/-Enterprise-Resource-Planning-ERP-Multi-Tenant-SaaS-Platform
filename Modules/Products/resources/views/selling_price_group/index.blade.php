@extends('layouts.app')
@section('title', __('Selling Price Group'))

@section('page-content')
    <div class="content container-fluid">

        <!-- Page Header -->
        <x-breadcrumb class="col">
            <x-slot name="title">@lang('Selling Price Group')</x-slot>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Products</a></li>
                <li class="breadcrumb-item active">@lang('Selling Price Group')</li>
            </ul>
            <x-slot name="right">
                <button type="button" class="btn btn-primary"
                    data-url="{{action([\Modules\Products\Http\Controllers\SellingPriceGroupController::class, 'create'])}}"
                    data-ajax-modal="true" data-title="@lang('Add Selling Price Group')" data-size="lg"
                    data-container=".view_modal">
                    <i class="fa fa-plus"></i> @lang('Add')
                </button>
            </x-slot>
        </x-breadcrumb>
        <!-- /Page Header -->

        <!-- Import/Export Section -->
        <div class="row mb-4">
            <div class="col-md-12">
                @component('components.widget', ['class' => 'box-primary', 'title' => __('Import/Export Selling Price Group Prices')])
                <div class="row">
                    <div class="col-sm-6">
                        <a href="{{action([\Modules\Products\Http\Controllers\SellingPriceGroupController::class, 'export'])}}"
                            class="btn btn-primary">@lang('Export Selling Price Group Prices')</a>
                    </div>
                    <div class="col-sm-6">
                        <form
                            action="{{ action([\Modules\Products\Http\Controllers\SellingPriceGroupController::class, 'import']) }}"
                            method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group mb-3">
                                <label for="product_group_prices">{{ __('File to import') . ':' }}</label>
                                <input type="file" name="product_group_prices" required class="form-control"
                                    id="product_group_prices">
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">@lang('Submit')</button>
                            </div>
                        </form>
                    </div>
                    <div class="col-sm-12 mt-3">
                        <h4>@lang('Instructions'):</h4>
                        <p>&bull; @lang('Export Selling Price Group Prices')</p>
                        <p>&bull; @lang('Update the migrated price in the file')</p>
                        <p>&bull; @lang('Import the migrated file')</p>
                    </div>
                </div>
                @endcomponent
            </div>
        </div>

        <!-- Selling Price Groups Table -->
        <div class="row">
            <div class="col-md-12">
                @component('components.widget', ['class' => 'box-primary', 'title' => __('All Selling Price Group')])
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="selling_price_group_table">
                        <thead>
                            <tr>
                                <th>@lang('Name')</th>
                                <th>@lang('Description')</th>
                                <th class="notexport">@lang('Action')</th>
                            </tr>
                        </thead>
                    </table>
                </div>
                @endcomponent
            </div>
        </div>

        <div class="modal fade view_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>
    </div>
@endsection

@push('page-scripts')
    @vite([
        "resources/js/datatables.js"
    ])
    <script>
        window.addEventListener('load', function () {
            if (typeof $ === 'undefined') {
                console.error('jQuery is not loaded');
                return;
            }

            var selling_price_group_table = $('#selling_price_group_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ action([\Modules\Products\Http\Controllers\SellingPriceGroupController::class, 'index']) }}",
                columnDefs: [{
                    "targets": 2,
                    "orderable": false,
                    "searchable": false
                }],
                columns: [
                    { data: 'name', name: 'name' },
                    { data: 'description', name: 'description' },
                    { data: 'action', name: 'action' }
                ],
                @include('layouts.partials.datatable_export_button')
                        dom:
                    "<'row'<'col-sm-12 mb-4'B>>" +
                    "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            });


            $(document).on('submit', 'form#selling_price_group_form', function (e) {
                e.preventDefault();
                var data = $(this).serialize();
                var form = $(this);
                var submitBtn = form.find('button[type="submit"]');
                submitBtn.attr('disabled', true);

                // Get the method from the form's _method field or default to POST
                var method = form.find('input[name="_method"]').val() || 'POST';

                $.ajax({
                    method: method,
                    url: $(this).attr("action"),
                    dataType: "json",
                    data: data,
                    success: function (result) {
                        if (result.success == true) {
                            $('#generalModalPopup').modal('hide');
                            $('div.view_modal').modal('hide');
                            if (typeof Toastify === 'function') {
                                Toastify({ text: result.msg, className: "success" }).showToast();
                            } else {
                                alert(result.msg);
                            }
                            selling_price_group_table.ajax.reload();
                        } else {
                            if (typeof Toastify === 'function') {
                                Toastify({ text: result.msg, className: "danger" }).showToast();
                            } else {
                                alert(result.msg);
                            }
                            submitBtn.attr('disabled', false);
                        }
                    },
                    error: function (xhr) {
                        submitBtn.attr('disabled', false);
                        console.log('Error details:', xhr);
                        var errorMsg = 'Error: ' + xhr.statusText;
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        alert(errorMsg);
                    }
                });
            });


            $(document).on('click', 'button.delete_spg_button', function () {
                var href = $(this).data('href');
                var confirmFunc = (typeof swal !== 'undefined') ?
                    function (callback) {
                        swal({
                            title: "Are you sure?",
                            text: "You won't be able to revert this!",
                            icon: 'warning',
                            buttons: true,
                            dangerMode: true,
                        }).then((willDelete) => {
                            if (willDelete) callback();
                        });
                    } :
                    function (callback) {
                        if (confirm("Are you sure?")) callback();
                    };

                confirmFunc(function () {
                    $.ajax({
                        method: "DELETE",
                        url: href,
                        dataType: "json",
                        data: { _token: "{{ csrf_token() }}" },
                        success: function (result) {
                            if (result.success == true) {
                                if (typeof Toastify === 'function') {
                                    Toastify({ text: result.msg, className: "success" }).showToast();
                                }
                                selling_price_group_table.ajax.reload();
                            } else {
                                if (typeof Toastify === 'function') {
                                    Toastify({ text: result.msg, className: "danger" }).showToast();
                                }
                            }
                        }
                    });
                });
            });

            $(document).on('click', 'button.delete_total_activate', function () {
                var href = $(this).data('href');
                var confirmFunc = (typeof swal !== 'undefined') ?
                    function (callback) {
                        swal({
                            title: "Are you sure?",
                            icon: "warning",
                            buttons: true,
                            dangerMode: true,
                        }).then((willDelete) => {
                            if (willDelete) callback();
                        });
                    } :
                    function (callback) {
                        if (confirm("Are you sure?")) callback();
                    };

                confirmFunc(function () {
                    $.ajax({
                        method: "GET",
                        url: href,
                        dataType: "json",
                        success: function (result) {
                            if (result.success == true) {
                                if (typeof Toastify === 'function') {
                                    Toastify({ text: result.msg, className: "success" }).showToast();
                                }
                                selling_price_group_table.ajax.reload();
                            } else {
                                if (typeof Toastify === 'function') {
                                    Toastify({ text: result.msg, className: "danger" }).showToast();
                                }
                            }
                        }
                    });
                });
            });
        });
    </script>
@endpush