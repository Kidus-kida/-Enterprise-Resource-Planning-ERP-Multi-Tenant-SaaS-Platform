@extends('layouts.app')

@push('page-style')
    
@endpush

@section('page-content')
    <div class="content container-fluid">

        <!-- Page Header -->
        <x-breadcrumb class="col">
            <x-slot name="title">{{ __('Purchase') }}</x-slot>
            <ul class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
                </li>
                <li class="breadcrumb-item active">
                    {{ __('Purchase') }}
                </li>
            </ul>
            <x-slot name="right">
                <div class="col-auto float-end ms-auto">
                    <a href="{{ route('purchase.create') }}" class="btn add-btn">
                        <i class="fa-solid fa-plus"></i> {{ __('Add Purchase') }}
                    </a>
                    <div class="view-icons">
                        <a href="{{ route('purchase.index', ['view' => 'grid']) }}" class="grid-view btn btn-link {{ request('view') == 'grid' ? 'active' : '' }}"><i class="fa fa-th"></i></a>
                        <a href="{{ route('purchase.index', ['view' => 'list']) }}" class="list-view btn btn-link {{ request('view') != 'grid' ? 'active' : '' }}"><i class="fa-solid fa-bars"></i></a>
                    </div>
                </div>
            </x-slot>
        </x-breadcrumb>
        <!-- /Page Header -->

        <!-- Search Filter -->
        <div class="card mb-4">
            <div class="card-body">
                <form class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <x-form.label>{{ __('Business Location') }}</x-form.label>
                            <x-form.select id="location_id" name="location_id">
                                <option value="">{{ __('All') }}</option>
                                @foreach($business_locations as $key => $value)
                                    <option value="{{ $key }}">{{ $value }}</option>
                                @endforeach
                            </x-form.select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <x-form.label>{{ __('Supplier') }}</x-form.label>
                            <x-form.select id="supplier_id" name="supplier_id">
                                <option value="">{{ __('All') }}</option>
                                @foreach($suppliers as $key => $value)
                                    <option value="{{ $key }}">{{ $value }}</option>
                                @endforeach
                            </x-form.select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <x-form.label>{{ __('Status') }}</x-form.label>
                            <x-form.select id="status" name="status">
                                <option value="">{{ __('All') }}</option>
                                @foreach($orderStatuses as $key => $value)
                                    <option value="{{ $key }}">{{ $value }}</option>
                                @endforeach
                            </x-form.select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <x-form.label>{{ __('Payment Status') }}</x-form.label>
                            <x-form.select id="payment_status" name="payment_status">
                                <option value="">{{ __('All') }}</option>
                                    <option value="Paid">Paid</option>
                                    <option value="Partial">Partial</option>
                                    <option value="Due">Due</option>
                                    <option value="Due">Overdue</option>
                            </x-form.select>
                        </div>
                    </div>
                    <div class="col-md-3">
                         <div class="form-group">
                            <x-form.label>{{ __('Received Date Range') }}</x-form.label>
                            <x-form.input type="date" class="form-control" id="date_range" name="date_range" placeholder="{{ __('Select Date Range') }}"/>
                        </div>
                    </div>
                    <div class="col-md-3" style="margin-top: 32px;">
                        <x-form.button class="btn">{{ __('Filter') }}</x-form.button>
                    </div>
                </form>
            </div>
        </div>
        <!-- /Search Filter -->

        @if(request('view') == 'grid')
            <div class="row staff-grid-row">
                @if (!empty($purchases))
                    @foreach ($purchases as $purchase)
                    <div class="col-md-4 col-sm-6 col-12 col-lg-4 col-xl-3">
                        <div class="profile-widget">
                            <div class="profile-img">
                                <a href="{{ route('purchase.show', $purchase->id) }}" class="avatar">
                                    <span class="avatar-title rounded-circle bg-primary-light">
                                        {{ substr($purchase->contact->name ?? '?', 0, 1) }}
                                    </span>
                                </a>
                            </div>
                            <div class="dropdown profile-action">
                                <a href="#" class="action-icon dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><i class="material-icons">more_vert</i></a>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <a class="dropdown-item" href="{{ route('purchase.edit', $purchase->id) }}">
                                        <i class="fa-solid fa-pencil m-r-5"></i>
                                        {{ __('Edit') }}
                                    </a>
                                </div>
                            </div>
                            <h4 class="user-name m-t-10 mb-0 text-ellipsis"><a href="{{ route('purchase.show', $purchase->id) }}">{{ $purchase->contact->name ?? __('Unknown') }}</a></h4>
                            <div class="small text-muted">{{ $purchase->ref_no }}</div>
                            <div class="small text-muted">{{ \Carbon\Carbon::parse($purchase->transaction_date)->format('Y-m-d') }}</div>
                            
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <span class="badge" style="color: black">
                                    {{ ucfirst($purchase->status) }}
                                </span>
                                <div class="fw-bold">{{ number_format($purchase->final_total, 2) }}</div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                @endif
            </div>
        @else
            @include('purchase::list')
        @endif
    </div>
@endsection

@push('page-script')
<script>
    window.addEventListener('load', function() {
        if ($('#purchase_table').length) {
            var purchase_table = $('#purchase_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('purchase.list') }}",
                columns: [
                    { data: 'transaction_date', name: 'transactions.transaction_date' },
                    { data: 'ref_no', name: 'transactions.ref_no' },
                    { data: 'supplier_name', name: 'contacts.name' },
                    { data: 'status', name: 'transactions.status' },
                    { data: 'payment_status', name: 'transactions.payment_status' },
                    { data: 'final_total', name: 'transactions.final_total' },
                    { data: 'due', name: 'due', orderable: false, searchable: false },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-end' }
                ]
            });
        }
    });
</script>
@endpush
