@extends('layouts.app')
@section('title', __('Merged Sub Categories'))

@section('page-content')
    <div class="content container-fluid">

        <!-- Page Header -->
        <x-breadcrumb class="col">
            <x-slot name="title">@lang('Merged Sub Categories')</x-slot>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Products</a></li>
                <li class="breadcrumb-item active">@lang('Merged Sub Categories')</li>
            </ul>
            <x-slot name="right">
                @can('category.create')
                    <button type="button" class="btn btn-primary"
                        data-url="{{action([\Modules\Products\Http\Controllers\MergedSubCategoryController::class, 'create'])}}"
                        data-ajax-modal="true" data-title="@lang('Merge Sub Category')" data-size="lg"
                        data-container=".category_modal">
                        <i class="fa fa-compress"></i> @lang('Merge Sub Category')
                    </button>
                @endcan
            </x-slot>
        </x-breadcrumb>
        <!-- /Page Header -->

        <div class="row">
            <div class="col-md-12">
                @component('components.widget', ['class' => 'box-primary', 'title' => __('All Merged Sub Categories')])
                @can('category.view')
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="merged_sub_category_table">
                            <thead>
                                <tr>
                                    <th>@lang('Date & Time')</th>
                                    <th>@lang('Category')</th>
                                    <th>@lang('Merged Sub Category Name')</th>
                                    <th>@lang('Merged Sub Categories')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('User')</th>
                                    <th class="notexport">@lang('Action')</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                @endcan
                @endcomponent
            </div>
        </div>

        <div class="modal fade category_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>
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

            var merged_sub_category_table = $('#merged_sub_category_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ action([\Modules\Products\Http\Controllers\MergedSubCategoryController::class, 'index']) }}",
                columnDefs: [{
                    "targets": 6,
                    "orderable": false,
                    "searchable": false
                }],
                columns: [
                    { data: 'date_and_time', name: 'date_and_time' },
                    { data: 'category_name', name: 'category_name' },
                    { data: 'merged_sub_category_name', name: 'merged_sub_category_name' },
                    { data: 'merged_sub_categories', name: 'merged_sub_categories' },
                    { data: 'status', name: 'status' },
                    { data: 'username', name: 'username' },
                    { data: 'action', name: 'action' },
                ],
                @include('layouts.partials.datatable_export_button')
                dom:
                    "<'row'<'col-sm-12 mb-4'B>>" +
                    "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            });

            $(document).on('click', 'button.add_merged_sub_category', function () {
                $.ajax({
                    method: 'post',
                    url: "{{action([\Modules\Products\Http\Controllers\MergedSubCategoryController::class, 'store'])}}",
                    data: {
                        _token: "{{ csrf_token() }}",
                        date_and_time: $('#date_and_time').val(),
                        merged_sub_category_name: $('#merged_sub_category_name').val(),
                        category_id: $('#category').val(),
                        sub_categories: $('#sub_categories').val(),
                        status: $('#status').val()
                    },
                    success: function (result) {
                        if (result.success == 1) {
                            if (typeof Toastify === 'function') {
                                Toastify({ text: result.msg, className: "success" }).showToast();
                            } else {
                                alert(result.msg);
                            }
                            $('#generalModalPopup').modal('hide');
                            $('.category_modal').modal('hide');
                        } else {
                            if (typeof Toastify === 'function') {
                                Toastify({ text: result.msg, className: "danger" }).showToast();
                            } else {
                                alert(result.msg);
                            }
                        }
                        merged_sub_category_table.ajax.reload();
                    },
                });
            });

            $(document).on('click', 'button.edit_merged_sub_category', function () {
                var id = $('#merge_id').val();
                $.ajax({
                    method: 'put',
                    url: "{{action([\Modules\Products\Http\Controllers\MergedSubCategoryController::class, 'index'])}}/" + id,
                    data: {
                        _token: "{{ csrf_token() }}",
                        date_and_time: $('#date_and_time').val(),
                        merged_sub_category_name: $('#merged_sub_category_name').val(),
                        category_id: $('#category').val(),
                        sub_categories: $('#sub_categories').val(),
                        status: $('#status').val()
                    },
                    success: function (result) {
                        if (result.success == 1) {
                            if (typeof Toastify === 'function') {
                                Toastify({ text: result.msg, className: "success" }).showToast();
                            } else {
                                alert(result.msg);
                            }
                            $('#generalModalPopup').modal('hide');
                            $('.category_modal').modal('hide');
                        } else {
                            if (typeof Toastify === 'function') {
                                Toastify({ text: result.msg, className: "danger" }).showToast();
                            } else {
                                alert(result.msg);
                            }
                        }
                        merged_sub_category_table.ajax.reload();
                    },
                });
            });

            // Handle Edit Button Click in Table to Load Modal manually if needed (though global handler usually does this)
            // Based on how other modules work, we likely just rely on data-href via global handler, 
            // but the original code had manual handlers. We will support global 'btn-modal' class behavior.

            $(document).on('click', 'button.edit_category_button', function (e) { // Action column uses this class
                e.preventDefault();
                var url = $(this).data('href');
                var container = '.category_modal';
                $.ajax({
                    url: url,
                    dataType: 'html',
                    success: function (result) {
                        $(container).html(result).modal('show');
                    }
                });
            });


            $(document).on('click', 'a.delete_merge_button', function (e) {
                e.preventDefault();
                var href = $(this).attr('href');
                var confirmFunc = (typeof swal !== 'undefined') ?
                    function (callback) {
                        swal({
                            title: "Are you sure?",
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
                                    Toastify({ text: result.msg, className: "success" }).showToast();
                                } else {
                                    alert(result.msg);
                                }
                            } else {
                                if (typeof Toastify === 'function') {
                                    Toastify({ text: result.msg, className: "danger" }).showToast();
                                } else {
                                    alert(result.msg);
                                }
                            }
                            merged_sub_category_table.ajax.reload();
                        },
                    });
                });
            });
        });
    </script>
@endpush