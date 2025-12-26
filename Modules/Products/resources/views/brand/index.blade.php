@extends('layouts.app')
@section('title', 'Brands')

@section('page-content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <x-breadcrumb class="col">
            <x-slot name="title">Brands</x-slot>
            <ul class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item active">
                    Brands
                </li>
            </ul>
            <x-slot name="right">
                @can('brand.create')
                    <button type="button" class="btn btn-primary"
                        data-url="{{action([\Modules\Products\Http\Controllers\BrandController::class, 'create'])}}"
                        data-ajax-modal="true" data-title="Add Brand" data-container=".brands_modal">
                        <i class="fa fa-plus"></i> Add</button>
                @endcan
            </x-slot>
        </x-breadcrumb>
        <!-- /Page Header -->

        <div class="card mb-3">
            <div class="card-body">
                @component('components.widget', ['class' => 'box-primary', 'title' => 'All your Brands'])
                @can('brand.view')
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="brands_table">
                            <thead>
                                <tr>
                                    <th>Brands</th>
                                    <th>Note</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                @endcan
                @endcomponent
            </div>
        </div>
    </div>

    <div class="modal fade brands_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
@endsection

@push('page-scripts')
    @vite([
        "resources/js/datatables.js"
    ])
    <script type="module">
        document.addEventListener('DOMContentLoaded', function () {
            // Brand Table Initialization
            var brands_table = $('#brands_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{action([\Modules\Products\Http\Controllers\BrandController::class, 'index'])}}',
                columnDefs: [{
                    "targets": 2, // Action column
                    "orderable": false,
                    "searchable": false
                }],
                columns: [
                    { data: 'name', name: 'name' },
                    { data: 'description', name: 'description' },
                    { data: 'action', name: 'action' }
                ]
            });

            // Form Submission Handler for Add Brand
            $(document).on('submit', 'form#brand_add_form', function (e) {
                e.preventDefault();
                $(this).find('button[type="submit"]').attr('disabled', true);
                var data = $(this).serialize();

                $.ajax({
                    method: 'POST',
                    url: $(this).attr('action'),
                    dataType: 'json',
                    data: data,
                    success: function (result) {
                        if (result.success === true) {
                            $('#generalModalPopup').modal('hide');
                            $('div.brands_modal').modal('hide');
                            Toastify({ text: result.msg, className: "success", }).showToast();
                            brands_table.ajax.reload();
                        } else {
                            Toastify({ text: result.msg, className: "danger", }).showToast();
                        }
                    }
                });
            });

            // Form Submission Handler for Edit Brand
            $(document).on('submit', 'form#brand_edit_form', function (e) {
                e.preventDefault();
                $(this).find('button[type="submit"]').attr('disabled', true);
                var data = $(this).serialize();

                $.ajax({
                    method: 'POST',
                    url: $(this).attr('action'),
                    dataType: 'json',
                    data: data,
                    success: function (result) {
                        if (result.success === true) {
                            $('#generalModalPopup').modal('hide');
                            $('div.brands_modal').modal('hide');
                            Toastify({ text: result.msg, className: "success", }).showToast();
                            brands_table.ajax.reload();
                        } else {
                            Toastify({ text: result.msg, className: "danger", }).showToast();
                        }
                    }
                });
            });

            // Delete Brand Handler (Native Confirm + AJAX)
            $(document).on('click', 'button.delete_brand_button', function (e) {
                e.preventDefault();
                if (confirm("Are you sure you want to delete this brand?")) {
                    var href = $(this).data('href');

                    $.ajax({
                        method: 'DELETE',
                        url: href,
                        dataType: 'json',
                        data: { _token: "{{ csrf_token() }}" },
                        success: function (result) {
                            if (result.success === true) {
                                Toastify({ text: result.msg, className: "success", }).showToast();
                                brands_table.ajax.reload();
                            } else {
                                Toastify({ text: result.msg, className: "danger", }).showToast();
                            }
                        },
                        error: function(xhr) {
                            Toastify({ text: "Error: " + xhr.statusText, className: "danger", }).showToast();
                        }
                    });
                }
            });
        });
    </script>
@endpush