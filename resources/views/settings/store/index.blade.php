@extends('layouts.app')

@section('page-content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Store List</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Settings</li>
                    </ul>
                </div>
                <div class="col-auto float-end ms-auto">
                    <a href="{{ action([\App\Http\Controllers\StoreController::class, 'create']) }}"
                        class="btn add-btn"><i class="fa fa-plus"></i> Add</a>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <div class="row">
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h4 class="card-title mb-0">
                            <i class="fa fa-filter"></i> Filters
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label">Business Location:</label>
                                    <select name="location_filter" id="location_filter" class="form-control select">
                                        <option value="">All Locations</option>
                                        @foreach ($locations as $id => $name)
                                            <option value="{{ $id }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h4 class="card-title mb-0">All your store</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="store_table">
                                <thead>
                                    <tr>
                                        <th>Location Id</th>
                                        <th>Location Name</th>
                                        <th>Name</th>
                                        <th>Address</th>
                                        <th>Contact</th>
                                        <th>Stock</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('page-script')
    <script>
        $(document).ready(function() {
            // Initialize Select2
            if ($('.select').length > 0) {
                $('.select').select2({
                    minimumResultsForSearch: -1,
                    width: '100%'
                });
            }

            // Initialize DataTable
            var table = $('#store_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ action([\App\Http\Controllers\StoreController::class, 'index']) }}',
                    data: function(d) {
                        d.location_id = $('#location_filter').val();
                    }
                },
                columns: [{
                        data: 'location_code',
                        name: 'bl.location_id'
                    },
                    {
                        data: 'location_name',
                        name: 'bl.name'
                    },
                    {
                        data: 'name',
                        name: 'stores.name'
                    },
                    {
                        data: 'address',
                        name: 'stores.address'
                    },
                    {
                        data: 'contact_number',
                        name: 'stores.contact_number'
                    },
                    {
                        data: 'stock',
                        name: 'stores.stock'
                    },
                    {
                        data: 'status',
                        name: 'stores.status',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            // Filter change event
            $('#location_filter').change(function() {
                table.draw();
            });

            // Delete store
            $(document).on('click', '.delete-store', function(e) {
                e.preventDefault();
                var storeId = $(this).data('id');
                
                if (confirm('Are you sure you want to delete this store?')) {
                    $.ajax({
                        url: '{{ url('settings/stores') }}/' + storeId,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                table.draw();
                                alert(response.msg);
                            } else {
                                alert(response.msg);
                            }
                        },
                        error: function() {
                            alert('Error deleting store');
                        }
                    });
                }
            });
        });
    </script>
@endpush
