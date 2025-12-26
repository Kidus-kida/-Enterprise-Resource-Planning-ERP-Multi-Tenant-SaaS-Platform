@extends('layouts.app')
@section('page-title')
    {{ __('Units') }}
@endsection

@section('page-content')
    <div class="content container-fluid">

        <!-- Page Header -->
        <x-breadcrumb class="col">
            <x-slot name="title">{{ __('Manage Your Units') }}</x-slot>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">{{ __('Units') }}</a></li>
                <li class="breadcrumb-item active">{{ __('Manage your units') }}</li>
            </ul>
            <x-slot name="right">
                @can('unit.create')
                    <button type="button" class="btn btn-primary"
                        data-url="{{action([\Modules\Products\Http\Controllers\UnitController::class, 'create'])}}"
                        data-ajax-modal="true" data-title="@lang('Add Unit')" data-size="lg" data-container=".unit_modal">
                        <i class="fa fa-plus"></i> Add
                    </button>
                @endcan
            </x-slot>
        </x-breadcrumb>
        <!-- /Page Header -->

        <div class="row">
            <div class="col-md-12">
                @component('components.widget', ['class' => 'box-primary', 'title' => __('All your units')])
                <input type="hidden" name="is_property" id="is_property" value="0">
                @can('unit.view')
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="unit_table">
                            <thead>
                                <tr>
                                    <th>@lang('Name')</th>
                                    <th>@lang('Short Name')</th>
                                    <th>@lang('Allow Decimal')</th>
                                    <th>@lang('Multiple Units')</th>
                                    <th>@lang('Connected Units')</th>
                                    <th class="notexport">@lang('Action')</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                @endcan
                @endcomponent
            </div>
        </div>

        <div class="modal fade unit_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>

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

            var is_property = $('#is_property').val();
            var units_table = $('#unit_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ action([\Modules\Products\Http\Controllers\UnitController::class, 'index']) }}?is_property=" + is_property,
                @include('layouts.partials.datatable_export_button')
                                columnDefs: [{ targets: 3, orderable: false, searchable: false }],
                columns: [
                    { data: 'actual_name', name: 'actual_name' },
                    { data: 'short_name', name: 'short_name' },
                    { data: 'allow_decimal', name: 'allow_decimal' },
                    { data: 'multiple_units', name: 'multiple_units' },
                    { data: 'connected_units', name: 'connected_units' },
                    { data: 'action', name: 'action' },
                ],
                dom:
                    "<'row'<'col-sm-12 mb-4'B>>" +
                    "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            });

            $(document).on('submit', 'form#unit_add_form', function (e) {
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
                            $('div.unit_modal').modal('hide');

                            if (typeof Toastify === 'function') {
                                Toastify({
                                    text: result.msg,
                                    className: "success",
                                }).showToast();
                            } else {
                                alert(result.msg);
                            }
                            units_table.ajax.reload();
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

            $(document).on('click', 'button.edit_unit_button', function (e) {
                e.preventDefault();
                var url = $(this).data('href');
                $('div.unit_modal').load(url, function (response, status, xhr) {
                    if (status == "error") {
                        alert("Error loading form: " + xhr.status + " " + xhr.statusText);
                        return;
                    }
                    $(this).modal('show');
                    $('form#unit_edit_form').off('submit').on('submit', function (e) {
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
                                    $('div.unit_modal').modal('hide');
                                    if (typeof Toastify === 'function') {
                                        Toastify({
                                            text: result.msg,
                                            className: "success",
                                        }).showToast();
                                    } else {
                                        alert(result.msg);
                                    }
                                    units_table.ajax.reload();
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

            $(document).on('click', 'button.delete_unit_button', function () {
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
                                units_table.ajax.reload();
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