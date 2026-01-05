@extends('layouts.app')

@section('page-content')
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Invoice Settings</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Settings</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3">
                <div class="sidebar text-start shadow-sm border rounded">
                    <div class="sidebar-inner slimscroll">
                        {!! renderAppMenu() !!}
                    </div>
                </div>
            </div>
            <!-- /Sidebar -->

            <div class="col-md-9">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <ul class="nav nav-tabs card-header-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#schemes_tab">Invoice Schemes</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#layouts_tab">Invoice Layouts</a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            <!-- Schemes Tab -->
                            <div class="tab-pane fade show active" id="schemes_tab">
                                <div class="row mb-3">
                                    <div class="col-12 text-end">
                                        <button type="button" class="btn btn-primary btn-modal"
                                            data-href="{{ action([\App\Http\Controllers\InvoiceSchemeController::class, 'create']) }}"
                                            data-container=".invoice_modal">
                                            <i class="fa fa-plus"></i> Add Scheme
                                        </button>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped" id="invoice_schemes_table">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Prefix</th>
                                                <th>Start Number</th>
                                                <th>Invoice Count</th>
                                                <th>Total Digits</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>

                            <!-- Layouts Tab -->
                            <div class="tab-pane fade" id="layouts_tab">
                                <div class="row mb-3">
                                    <div class="col-12 text-end">
                                        <a href="{{ action([\App\Http\Controllers\InvoiceLayoutController::class, 'create']) }}"
                                            class="btn btn-primary">
                                            <i class="fa fa-plus"></i> Add Layout
                                        </a>
                                    </div>
                                </div>
                                <div class="row">
                                    @forelse($invoice_layouts as $layout)
                                        <div class="col-md-4 mb-3">
                                            <div class="card border">
                                                <div class="card-body text-center">
                                                    <div class="mb-3">
                                                        <a href="{{ action([\App\Http\Controllers\InvoiceLayoutController::class, 'edit'], [$layout->id]) }}"
                                                            class="text-decoration-none text-dark">
                                                            <i class="fa fa-file-invoice fa-4x text-muted"></i>
                                                            <h5 class="mt-2">{{ $layout->name }}</h5>
                                                        </a>
                                                    </div>

                                                    @if ($layout->is_default)
                                                        <span class="badge bg-success">Default</span>
                                                    @endif

                                                    <div class="mt-2">
                                                        <a href="{{ action([\App\Http\Controllers\InvoiceLayoutController::class, 'edit'], [$layout->id]) }}"
                                                            class="btn btn-sm btn-outline-primary">Edit</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="col-12">
                                            <p class="text-center">No layouts found.</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade invoice_modal" tabindex="-1" role="dialog" aria-hidden="true"></div>
@endsection

@push('page-script')
    <script>
        $(document).ready(function() {
            // DataTables
            var invoice_schemes_table = $('#invoice_schemes_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ action([\App\Http\Controllers\InvoiceSchemeController::class, 'index']) }}',
                columns: [{
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'prefix',
                        name: 'prefix'
                    },
                    {
                        data: 'start_number',
                        name: 'start_number'
                    },
                    {
                        data: 'invoice_count',
                        name: 'invoice_count'
                    },
                    {
                        data: 'total_digits',
                        name: 'total_digits'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ]
            });

            $(document).on('click', '.set_default_invoice', function(e) {
                e.preventDefault();
                $.ajax({
                    url: $(this).attr('href'),
                    dataType: 'json',
                    success: function(result) {
                        if (result.success == true) {
                            toastr.success(result.msg);
                            invoice_schemes_table.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    }
                });
            });

            $(document).on('click', '.delete_invoice_button', function(e) {
                e.preventDefault();
                if (confirm('Are you sure you want to delete this scheme?')) {
                    $.ajax({
                        url: $(this).attr('href'),
                        dataType: 'json',
                        success: function(result) {
                            if (result.success == true) {
                                toastr.success(result.msg);
                                invoice_schemes_table.ajax.reload();
                            } else {
                                toastr.error(result.msg);
                            }
                        }
                    });
                }
            });

            // Modal handling handled by app.js usually, but ensuring init here if generic
        });
    </script>
@endpush
