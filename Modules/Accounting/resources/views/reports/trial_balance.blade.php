@extends('layouts.app')

@section('page-content')
    <div class="content container-fluid">
        <x-breadcrumb class="col">
            <x-slot name="title">{{ __('Trial Balance') }}</x-slot>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                <li class="breadcrumb-item active">{{ __('Trial Balance') }}</li>
            </ul>
        </x-breadcrumb>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <!-- Date Filter -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <label>{{ __('From Date') }}</label>
                                <input type="date" id="from_date" class="form-control" value="{{ date('Y-m-01') }}">
                            </div>
                            <div class="col-md-3">
                                <label>{{ __('To Date') }}</label>
                                <input type="date" id="to_date" class="form-control" value="{{ date('Y-m-d') }}">
                            </div>
                            <div class="col-md-3">
                                <label>&nbsp;</label><br>
                                <button type="button" id="load_report" class="btn btn-primary">{{ __('Load Report') }}</button>
                                <button type="button" onclick="window.print()" class="btn btn-secondary">{{ __('Print') }}</button>
                            </div>
                        </div>

                        <!-- Report Content -->
                        <div id="report_content">
                            <div class="text-center mb-4">
                                <h3>{{ __('Trial Balance') }}</h3>
                                <p id="report_period" class="text-muted"></p>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>{{ __('Account Number') }}</th>
                                            <th>{{ __('Account Name') }}</th>
                                            <th class="text-end" width="150">{{ __('Debit') }}</th>
                                            <th class="text-end" width="150">{{ __('Credit') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="trial_balance_accounts">
                                    </tbody>
                                    <tfoot class="table-light fw-bold">
                                        <tr>
                                            <td colspan="2">{{ __('TOTAL') }}</td>
                                            <td class="text-end" id="total_debit">0.00</td>
                                            <td class="text-end" id="total_credit">0.00</td>
                                        </tr>
                                        <tr id="difference_row" class="table-danger" style="display: none;">
                                            <td colspan="2">{{ __('DIFFERENCE') }}</td>
                                            <td colspan="2" class="text-end" id="difference">0.00</td>
                                        </tr>
                                    </tfoot>
                                </table>

                                <div id="balance_status" class="alert mt-3" style="display: none;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('page-scripts')
<script type="module">
    function loadReport() {
        $.ajax({
            url: "{{ route('accounting.trial-balance') }}",
            data: {
                from_date: $('#from_date').val(),
                to_date: $('#to_date').val()
            },
            success: function(response) {
                $('#report_period').text(response.period);
                
                let accountsHtml = '';
                response.accounts.forEach(function(acc) {
                    accountsHtml += `<tr>
                        <td>${acc.account_number}</td>
                        <td>${acc.name}</td>
                        <td class="text-end">${acc.debit}</td>
                        <td class="text-end">${acc.credit}</td>
                    </tr>`;
                });
                $('#trial_balance_accounts').html(accountsHtml);
                
                $('#total_debit').text(response.total_debit);
                $('#total_credit').text(response.total_credit);
                
                // Check if balanced
                if (response.difference_raw == 0) {
                    $('#difference_row').hide();
                    $('#balance_status').removeClass('alert-danger').addClass('alert-success');
                    $('#balance_status').html('<i class="fa fa-check-circle"></i> {{ __("Trial Balance is balanced!") }}');
                    $('#balance_status').show();
                } else {
                    $('#difference').text(response.difference);
                    $('#difference_row').show();
                    $('#balance_status').removeClass('alert-success').addClass('alert-danger');
                    $('#balance_status').html('<i class="fa fa-exclamation-triangle"></i> {{ __("Trial Balance is NOT balanced. Please check your entries.") }}');
                    $('#balance_status').show();
                }
            }
        });
    }

    $(document).ready(function() {
        loadReport();
        $('#load_report').click(function() {
            loadReport();
        });
    });
</script>
@endpush
