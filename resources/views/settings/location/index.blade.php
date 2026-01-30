@extends('pages.settings.index')

@section('title', __('business.business_locations'))

@section('page-header-section')
    <x-breadcrumb> 
        <x-slot name="title">@lang('business.business_locations') </x-slot> 
        <x-slot name="right">
            <div class="col-auto float-end ms-auto">
                <a href="{{ action([\App\Http\Controllers\BusinessLocationController::class, 'create']) }}"
                    class="btn add-btn"><i class="fa fa-plus"></i> Add Location</a>
            </div>
        </x-slot>
    </x-breadcrumb>
@endsection

@section('page-section')


        <div class="row">
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h4 class="card-title mb-0">All Business Locations</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="business_location_table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Location ID</th>
                                        <th>Landmark</th>
                                        <th>City</th>
                                        <th>Zip Code</th>
                                        <th>State</th>
                                        <th>Country</th>
                                        <th>Price Group</th>
                                        <th>Invoice Scheme</th>
                                        <th>Invoice Layout</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
@endsection

@push('page-script')
    <script>
        $(document).ready(function() {
            $('#business_location_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ action([\App\Http\Controllers\BusinessLocationController::class, 'index']) }}',
                columns: [{
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'location_id',
                        name: 'location_id'
                    },
                    {
                        data: 'landmark',
                        name: 'landmark'
                    },
                    {
                        data: 'city',
                        name: 'city'
                    },
                    {
                        data: 'zip_code',
                        name: 'zip_code'
                    },
                    {
                        data: 'state',
                        name: 'state'
                    },
                    {
                        data: 'country',
                        name: 'country'
                    },
                    {
                        data: 'price_group',
                        name: 'spg.name'
                    },
                    {
                        data: 'invoice_scheme',
                        name: 'ic.name'
                    },
                    {
                        data: 'invoice_layout',
                        name: 'il.name'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ]
            });
        });
    </script>
@endpush
