@extends('layouts.app')
@section('page-title')
    Categories
@endsection

@section('page-content')
    <div class="content container-fluid">

        <!-- Page Header -->
        <x-breadcrumb class="col">
            <x-slot name="title">Manage your Categories</x-slot>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Categories</a></li>
                <li class="breadcrumb-item active">Manage your Categories</li>
            </ul>
            <x-slot name="right">
                @can('category.create')
                    <button type="button" class="btn btn-primary"
                        data-url="{{action([\Modules\Products\Http\Controllers\CategoryController::class, 'create'])}}"
                        data-ajax-modal="true" data-title="Add Category" data-size="lg" data-container=".category_modal">
                        <i class="fa fa-plus"></i> Add
                    </button>
                @endcan
            </x-slot>
        </x-breadcrumb>
        <!-- /Page Header -->

        <div class="row">
            <div class="col-md-12">
                @component('components.widget', ['class' => 'box-primary', 'title' => 'Manage your Categories'])
                @can('category.view')
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="all_category_table">
                            <thead>
                                <tr>
                                    <th style="width:25px">Category</th>
                                    <th>Code</th>
                                    <th style="width:25px">Sub Category</th>
                                    <th>Sub Cat Code</th>
                                    <th style="width:50px">Account Group (COGS)</th>
                                    <th style="width:50px">Sales Income Account</th>
                                    <th>VAT based on</th>
                                    <th style="width:150px">Apply VAT on</th>
                                    <th>Vat Exempted</th>
                                    <th class="notexport">Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                @endcan
                @endcomponent
            </div>
        </div>

        <div class="modal fade category_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        </div>

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

            var category_table = $('#all_category_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ action([\Modules\Products\Http\Controllers\CategoryController::class, 'index']) }}",
                    error: function (xhr, error, code) {
                        console.log('Ajax error:', xhr, error, code);
                        console.log('Response:', xhr.responseText);
                        alert('Error loading categories. Check console for details.');
                    }
                },
                @include('layouts.partials.datatable_export_button')
                    columns: [
                    { data: 'category_name', name: 'name', defaultContent: '' },
                    { data: 'category_short_code', name: 'short_code', defaultContent: '' },
                    { data: 'sub_category_name', name: 'name', defaultContent: '' },
                    { data: 'sub_category_short_code', name: 'short_code', defaultContent: '' },
                    { data: 'cogs', name: 'cogs', defaultContent: '' },
                    { data: 'sales_accounts', name: 'sales_accounts', defaultContent: '' },
                    { data: 'vat_based_on', name: 'vat_based_on', defaultContent: '' },
                    { data: 'apply_vat_on', name: 'apply_vat_on', defaultContent: '' },
                    { data: 'vat_exempted', name: 'vat_exempted', defaultContent: '' },
                    { data: 'action', name: 'action', orderable: false, searchable: false },
                ],
                dom:
                    "<'row'<'col-sm-12 mb-4'B>>" +
                    "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            });

            $(document).on('submit', 'form#category_add_form', function (e) {
                e.preventDefault();
                var form = $(this);
                var submitBtn = form.find('button[type="submit"]');
                submitBtn.attr('disabled', true);
                var data = form.serialize();
                $.ajax({
                    method: 'POST',
                    url: form.attr('action'),
                    dataType: 'json',
                    data: data,
                    success: function (result) {
                        if (result.success == true) {
                            $('#generalModalPopup').modal('hide');
                            $('div.category_modal').modal('hide');
                            if (typeof Toastify === 'function') {
                                Toastify({
                                    text: result.msg,
                                    className: "success",
                                }).showToast();
                            } else {
                                alert(result.msg);
                            }
                            category_table.ajax.reload();
                        } else {
                            if (typeof Toastify === 'function') {
                                Toastify({
                                    text: result.msg,
                                    className: "danger",
                                }).showToast();
                            } else {
                                alert(result.msg);
                            }
                            submitBtn.attr('disabled', false);
                        }
                    },
                    error: function (xhr) {
                        submitBtn.attr('disabled', false);
                        alert('Error: ' + xhr.statusText);
                    }
                });
            });

            $(document).on('click', 'button.edit_category_button', function (e) {
                e.preventDefault();
                var url = $(this).data('href');
                $('div.category_modal').load(url, function (response, status, xhr) {
                    if (status == "error") {
                        alert("Error loading form: " + xhr.status + " " + xhr.statusText);
                        return;
                    }
                    $(this).modal('show');
                    $('form#category_edit_form').off('submit').on('submit', function (e) {
                        e.preventDefault();
                        var form = $(this);
                        var submitBtn = form.find('button[type="submit"]');
                        submitBtn.attr('disabled', true);
                        var data = form.serialize();
                        $.ajax({
                            method: 'POST',
                            url: form.attr('action'),
                            dataType: 'json',
                            data: data,
                            success: function (result) {
                                if (result.success == true) {
                                    $('div.category_modal').modal('hide');
                                    if (typeof Toastify === 'function') {
                                        Toastify({
                                            text: result.msg,
                                            className: "success",
                                        }).showToast();
                                    } else {
                                        alert(result.msg);
                                    }
                                    category_table.ajax.reload();
                                } else {
                                    if (typeof Toastify === 'function') {
                                        Toastify({
                                            text: result.msg,
                                            className: "danger",
                                        }).showToast();
                                    } else {
                                        alert(result.msg);
                                    }
                                    submitBtn.attr('disabled', false);
                                }
                            },
                            error: function (xhr) {
                                submitBtn.attr('disabled', false);
                                alert('Error: ' + xhr.statusText);
                            }
                        });
                    });
                });
            });

            $(document).on('click', 'button.delete_category_button', function () {
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
                        method: 'DELETE',
                        url: href,
                        dataType: 'json',
                        data: { _token: "{{ csrf_token() }}" },
                        success: function (result) {
                            if (result.success == true) {
                                if (typeof Toastify === 'function') {
                                    Toastify({
                                        text: result.msg,
                                        className: "success",
                                    }).showToast();
                                } else {
                                    alert(result.msg);
                                }
                                category_table.ajax.reload();
                            } else {
                                if (typeof Toastify === 'function') {
                                    Toastify({
                                        text: result.msg,
                                        className: "danger",
                                    }).showToast();
                                } else {
                                    alert(result.msg);
                                }
                            }
                        },
                        error: function (xhr) {
                            alert('Error: ' + xhr.statusText);
                        }
                    });
                });
            });
        });
    </script>
@endpush