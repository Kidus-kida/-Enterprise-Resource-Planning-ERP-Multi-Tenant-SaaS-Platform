@extends('layouts.app')

@section('page-content')
    <div class="content container-fluid">
        <x-breadcrumb class="col">
            <x-slot name="title">{{ __('Income Statement') }}</x-slot>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                <li class="breadcrumb-item active">{{ __('Income Statement') }}</li>
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
                                <h3>{{ __('Income Statement') }}</h3>
                                <p id="report_period" class="text-muted"></p>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <!-- Income Section -->
                                    <h5 class="mt-4">{{ __('Income') }}</h5>
                                    <table class="table table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>{{ __('Account') }}</th>
                                                <th class="text-end" width="200">{{ __('Amount') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody id="income_accounts">
                                        </tbody>
                                        <tfoot class="table-light">
                                            <tr class="fw-bold">
                                                <td>{{ __('Total Income') }}</td>
                                                <td class="text-end" id="total_income">0.00</td>
                                            </tr>
                                        </tfoot>
                                    </table>

                                    <!-- Expenses Section -->
                                    <h5 class="mt-4">{{ __('Expenses') }}</h5>
                                    <table class="table table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>{{ __('Account') }}</th>
                                                <th class="text-end" width="200">{{ __('Amount') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody id="expense_accounts">
                                        </tbody>
                                        <tfoot class="table-light">
                                            <tr class="fw-bold">
                                                <td>{{ __('Total Expenses') }}</td>
                                                <td class="text-end" id="total_expenses">0.00</td>
                                            </tr>
                                        </tfoot>
                                    </table>

                                    <!-- Net Income -->
                                    <table class="table table-bordered mt-4">
                                        <tr class="table-primary fw-bold">
                                            <td>{{ __('NET INCOME (LOSS)') }}</td>
                                            <td class="text-end" width="200" id="net_income">0.00</td>
                                        </tr>
                                    </table>
                                </div>
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
            url: "{{ route('accounting.income-statement') }}",
            data: {
                from_date: $('#from_date').val(),
                to_date: $('#to_date').val()
            },
            success: function(response) {
                $('#report_period').text(response.period);
                
                let incomeHtml = '';
                response.income_accounts.forEach(function(acc) {
                    incomeHtml += `<tr><td>${acc.name}</td><td class="text-end">${acc.amount}</td></tr>`;
                });
                $('#income_accounts').html(incomeHtml);
                $('#total_income').text(response.total_income);
                
                let expenseHtml = '';
                response.expense_accounts.forEach(function(acc) {
                    expenseHtml += `<tr><td>${acc.name}</td><td class="text-end">${acc.amount}</td></tr>`;
                });
                $('#expense_accounts').html(expenseHtml);
                $('#total_expenses').text(response.total_expenses);
                
                $('#net_income').text(response.net_income);
                $('#net_income').parent().removeClass('table-success table-danger');
                $('#net_income').parent().addClass(response.net_income_raw >= 0 ? 'table-success' : 'table-danger');
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
