@extends('layouts.app')
@section('title', 'Categories')

@section('page-content')
<div class="content container-fluid">
    <!-- Page Header -->
    <x-breadcrumb class="col">
        <x-slot name="title">Categories</x-slot>
        <ul class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('dashboard') }}">Dashboard</a>
            </li>
            <li class="breadcrumb-item active">
                Categories
            </li>
        </ul>
        <x-slot name="right">
            @can('category.create')
                <button type="button" class="btn btn-primary" 
                    data-url="{{ route('products.categories.create') }}" 
                    data-ajax-modal="true" data-title="Add Category" data-container=".category_modal">
                    <i class="fa fa-plus"></i> Add</button>
            @endcan
        </x-slot>
    </x-breadcrumb>
    <!-- /Page Header -->

    <div class="card mb-3">
        <div class="card-body">
            @component('components.widget', ['class' => 'box-primary', 'title' => 'Manage your Categories'])
                @can('category.view')
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="all_category_table" >
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
</div>

<div class="modal fade category_modal" role="dialog" 
    aria-labelledby="gridSystemModalLabel">
</div>
@endsection

@push('page-scripts')
    @vite([
        "resources/js/datatables.js"
    ])
    <script type="module">
    document.addEventListener('DOMContentLoaded', function() {
        var category_table = $('#all_category_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('products.categories.index') }}",
            },
            columns: [
                { data: 'category_name', name: 'name' },
                { data: 'category_short_code', name: 'short_code' },
                { data: 'sub_category_name', name: 'name' },
                { data: 'sub_category_short_code', name: 'short_code' },
                { data: 'cogs', name: 'cogs' },
                { data: 'sales_accounts', name: 'sales_accounts' },
                { data: 'vat_based_on', name: 'vat_based_on' },
                { data: 'apply_vat_on', name: 'apply_vat_on' },
                { data: 'vat_exempted', name: 'vat_exempted' },
                { data: 'action', name: 'action', orderable: false, searchable: false },
            ]
        });

        // Form Submission Handler for Add Category
        $(document).on('submit', 'form#category_add_form', function(e) {
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
                        $('div.category_modal').modal('hide');
                        Toastify({ text: result.msg, className: "success", }).showToast();
                        category_table.ajax.reload();
                    } else {
                        Toastify({ text: result.msg, className: "danger", }).showToast();
                    }
                }
            });
        });

        // Form Submission Handler for Edit Category
        $(document).on('submit', 'form#category_edit_form', function(e) {
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
                        $('div.category_modal').modal('hide');
                        Toastify({ text: result.msg, className: "success", }).showToast();
                        category_table.ajax.reload();
                    } else {
                        Toastify({ text: result.msg, className: "danger", }).showToast();
                    }
                }
            });
        });

        // Delete Category Handler (Native Confirm + AJAX)
        $(document).on('click', 'button.delete_category_button', function(e) {
            e.preventDefault();
            if (confirm("Are you sure you want to delete this category?")) {
                var href = $(this).data('href');

                $.ajax({
                    method: 'DELETE',
                    url: href,
                    dataType: 'json',
                    success: function(result) {
                        if (result.success === true) {
                            Toastify({ text: result.msg, className: "success", }).showToast();
                            category_table.ajax.reload();
                        } else {
                            Toastify({ text: result.msg, className: "danger", }).showToast();
                        }
                    }
                });
            }
        });
    });
    </script>
@endpush