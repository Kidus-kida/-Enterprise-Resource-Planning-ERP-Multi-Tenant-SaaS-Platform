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
                                <span class="badge bg-inverse-{{ $purchase->status == 'received' ? 'success' : ($purchase->status == 'pending' ? 'warning' : 'danger') }}">
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
            <div class="row">
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table class="table table-striped custom-table" id="purchase_table">
                            <thead>
                                <tr>
                                     <th>{{ __('Date') }}</th>
                                     <th>{{ __('Ref No') }}</th>
                                     <th>{{ __('Supplier') }}</th>
                                     <th>{{ __('Status') }}</th>
                                     <th>{{ __('Payment Status') }}</th>
                                     <th>{{ __('Grand Total') }}</th>
                                     <th>{{ __('Due') }}</th>
                                     <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    @if(request('view') != 'grid')
    <script>
        $(document).ready(function() {
            if ($('#purchase_table').length > 0) {
                $('#purchase_table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: '{{ route("purchase.index") }}',
                    columns: [
                        { data: 'transaction_date', name: 'transaction_date' },
                        { data: 'ref_no', name: 'ref_no' },
                        { data: 'supplier', name: 'supplier', orderable: false, searchable: false },
                        { data: 'status', name: 'status' },
                        { data: 'payment_status', name: 'payment_status' },
                        { data: 'final_total', name: 'final_total' },
                        { data: 'payment_due', name: 'payment_due', orderable: false, searchable: false },
                        { data: 'action', name: 'action', orderable: false, searchable: false },
                    ]
                });
            }
        });
    </script>
    @endif
@endpush
