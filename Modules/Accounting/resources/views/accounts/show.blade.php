@extends('layouts.app')

@section('page-content')
    <div class="content container-fluid">

        <!-- Page Header -->
        <x-breadcrumb class="col">
            <x-slot name="title">{{ __('Account Details') }}</x-slot>
            <ul class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('accounts.index') }}">{{ __('Accounts') }}</a>
                </li>
                <li class="breadcrumb-item active">{{ $account->name }}</li>
            </ul>
        </x-breadcrumb>
        <!-- /Page Header -->

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">{{ $account->name }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <th width="200">{{ __('Account Number') }}:</th>
                                        <td>{{ $account->account_number }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Account Name') }}:</th>
                                        <td>{{ $account->name }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Account Type') }}:</th>
                                        <td>{{ optional($account->accountType)->name ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Account Group') }}:</th>
                                        <td>{{ optional($account->accountGroup)->name ?? 'N/A' }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <th width="200">{{ __('Current Balance') }}:</th>
                                        <td class="fw-bold {{ $currentBalance >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ number_format($currentBalance, 2) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Status') }}:</th>
                                        <td>
                                            @if($account->is_closed)
                                                <span class="badge bg-danger">{{ __('Closed') }}</span>
                                            @elseif($account->disabled)
                                                <span class="badge bg-warning">{{ __('Disabled') }}</span>
                                            @else
                                                <span class="badge bg-success">{{ __('Active') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Note') }}:</th>
                                        <td>{{ $account->note ?? '-' }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <hr>

                        <!-- Date Filter -->
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label>{{ __('Start Date') }}</label>
                                <input type="date" id="start_date" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label>{{ __('End Date') }}</label>
                                <input type="date" id="end_date" class="form-control" value="{{ date('Y-m-d') }}">
                            </div>
                            <div class="col-md-3">
                                <label>&nbsp;</label><br>
                                <button type="button" id="filter_btn" class="btn btn-primary">{{ __('Filter') }}</button>
                            </div>
                        </div>

                        <!-- Account Ledger -->
                        <h5>{{ __('Account Ledger') }}</h5>
                        <div class="table-responsive">
                            <table class="table table-striped" id="ledger_table">
                                <thead>
                                    <tr>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Reference') }}</th>
                                        <th>{{ __('Remark') }}</th>
                                        <th class="text-end">{{ __('Debit') }}</th>
                                        <th class="text-end">{{ __('Credit') }}</th>
                                        <th class="text-end">{{ __('Balance') }}</th>
                                        <th class="text-end">{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                                <tfoot>
                                    <tr class="fw-bold">
                                        <td colspan="3">{{ __('Total') }}</td>
                                        <td class="text-end" id="total_debit">0.00</td>
                                        <td class="text-end" id="total_credit">0.00</td>
                                        <td class="text-end" id="total_balance">0.00</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('page-scripts')
<script type="module">
    function loadLedger() {
        $.ajax({
            url: "{{ route('accounts.show', $account->id) }}",
            data: {
                start_date: $('#start_date').val(),
                end_date: $('#end_date').val()
            },
            success: function(response) {
                let tbody = '';
                response.data.forEach(function(row) {
                    tbody += `<tr>
                        <td>${row.operation_date}</td>
                        <td>${row.reference}</td>
                        <td>${row.remark}</td>
                        <td class="text-end">${row.debit}</td>
                        <td class="text-end">${row.credit}</td>
                        <td class="text-end">${row.balance}</td>
                        <td class="text-end">${row.action}</td>
                    </tr>`;
                });
                $('#ledger_table tbody').html(tbody);
                
                $('#total_debit').text(response.totals.debit);
                $('#total_credit').text(response.totals.credit);
                $('#total_balance').text(response.totals.balance);
            }
        });
    }

    $(document).ready(function() {
        loadLedger();
        
        $('#filter_btn').click(function() {
            loadLedger();
        });
    });
</script>
@endpush
