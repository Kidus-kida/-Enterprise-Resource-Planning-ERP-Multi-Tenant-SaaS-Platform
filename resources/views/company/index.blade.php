@extends('layouts.app')
@section('title', __('company.companies'))

@section('page-content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang('company.companies')
        <small>@lang('company.manage_your_companies')</small>
    </h1>
</section>

<!-- Main content -->
<section class="content">
    @component('components.widget', ['class' => 'box-primary', 'title' => __('company.all_your_companies')])
        @slot('tool')
            <div class="box-tools">
                <button type="button" class="btn btn-block btn-primary btn-modal" 
                    data-href="{{action([\App\Http\Controllers\CompanyController::class, 'create'])}}" 
                    data-container=".companies_modal">
                    <i class="fa fa-plus"></i> @lang('messages.add')
                </button>
            </div>
        @endslot
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="companies_table">
                <thead>
                    <tr>
                        <th>@lang('company.name')</th>
                        <th>@lang('company.tax_number')</th>
                        <th>@lang('company.is_default')</th>
                        <th>@lang('messages.action')</th>
                    </tr>
                </thead>
            </table>
        </div>
    @endcomponent

    <div class="modal fade companies_modal" tabindex="-1" role="dialog" 
        aria-labelledby="gridSystemModalLabel">
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="delete_company_modal" tabindex="-1" role="dialog" aria-labelledby="deleteCompanyModalLabel">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteCompanyModalLabel">
                        <i class="fa fa-exclamation-triangle"></i> Confirm Company Deletion
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning mb-3">
                        <strong><i class="fa fa-warning"></i> Warning!</strong> This action cannot be undone.
                    </div>
                    <p class="mb-3">Deleting this company will <strong>permanently remove ALL related data</strong> including:</p>
                    <ul class="list-unstyled ms-3">
                        <li><i class="fa fa-check text-danger"></i> Transactions</li>
                        <li><i class="fa fa-check text-danger"></i> Contacts (Clients/Suppliers)</li>
                        <li><i class="fa fa-check text-danger"></i> Products</li>
                        <li><i class="fa fa-check text-danger"></i> Employees</li>
                        <li><i class="fa fa-check text-danger"></i> Reports</li>
                        <li><i class="fa fa-check text-danger"></i> Settings</li>
                    </ul>
                    <p class="mt-3 mb-0"><strong>Are you absolutely sure you want to proceed?</strong></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fa fa-times"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-danger" id="confirm_delete_company">
                        <i class="fa fa-trash"></i> Yes, Delete Company
                    </button>
                </div>
            </div>
        </div>
    </div>

</section>
<!-- /.content -->

@endsection

@push('page-script')
<script type="module">
    $(document).ready(function(){
        var companies_table = $('#companies_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ action([\App\Http\Controllers\CompanyController::class, 'index']) }}',
            columnDefs: [ {
                "targets": 3,
                "orderable": false,
                "searchable": false
            } ],
            columns: [
                { data: 'name', name: 'name' },
                { data: 'tax_number', name: 'tax_number' },
                { data: 'is_default', name: 'is_default' },
                { data: 'action', name: 'action' }
            ]
        });

        $(document).on('submit', 'form#company_add_form', function(e) {
            e.preventDefault();
            $(this).find('button[type="submit"]').attr('disabled', true);
            var data = $(this).serialize();

            $.ajax({
                method: 'POST',
                url: $(this).attr('action'),
                dataType: 'json',
                data: data,
                success: function(result) {
                    if (result.success == true) {
                        $('div.companies_modal').modal('hide');
                        toastr.success(result.msg);
                        companies_table.ajax.reload();
                    } else {
                        toastr.error(result.msg);
                        $('form#company_add_form').find('button[type="submit"]').attr('disabled', false);
                    }
                }
            });
        });


        // Store delete URL globally for confirmation
        var deleteCompanyUrl = null;

        $(document).on('click', '.delete_company_button', function(e) {
            e.preventDefault();
            var $button = $(this);
            var href = $button.data('href');
            
            if (!href) {
                toastr.error('Delete URL not found');
                return;
            }
            
            // Store URL and show modal
            deleteCompanyUrl = href;
            $('#delete_company_modal').modal('show');
        });

        // Handle confirm delete button in modal
        $(document).on('click', '#confirm_delete_company', function() {
            if (!deleteCompanyUrl) {
                return;
            }

            // Disable button to prevent double-clicks
            $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Deleting...');
            
            $.ajax({
                method: "DELETE",
                url: deleteCompanyUrl,
                dataType: "json",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(result) {
                    $('#delete_company_modal').modal('hide');
                    if (result.success == true) {
                        toastr.success(result.msg);
                        companies_table.ajax.reload();
                    } else {
                        toastr.error(result.msg);
                    }
                    // Reset button
                    $('#confirm_delete_company').prop('disabled', false).html('<i class="fa fa-trash"></i> Yes, Delete Company');
                    deleteCompanyUrl = null;
                },
                error: function(xhr) {
                    $('#delete_company_modal').modal('hide');
                    toastr.error('An error occurred while deleting the company.');
                    // Reset button
                    $('#confirm_delete_company').prop('disabled', false).html('<i class="fa fa-trash"></i> Yes, Delete Company');
                    deleteCompanyUrl = null;
                }
            });
        });

        // Reset when modal is closed
        $('#delete_company_modal').on('hidden.bs.modal', function() {
            deleteCompanyUrl = null;
            $('#confirm_delete_company').prop('disabled', false).html('<i class="fa fa-trash"></i> Yes, Delete Company');
        });
        $(document).on('click', '.btn-modal', function(e) {
            e.preventDefault();
            var container = $(this).data('container');
            
            // Ensure container exists
            if (!$(container).length) {
                if (container.startsWith('.')) {
                     var className = container.substring(1);
                     $('body').append('<div class="modal fade ' + className + '" tabindex="-1" role="dialog"></div>');
                } else {
                     $('body').append('<div class="modal fade companies_modal" tabindex="-1" role="dialog"></div>');
                     container = '.companies_modal';
                }
            }

            $.ajax({
                url: $(this).data('href'),
                dataType: 'html',
                success: function(result) {
                    $(container).html(result).modal('show');
                }
            });
        });

    });
</script>
@endpush
