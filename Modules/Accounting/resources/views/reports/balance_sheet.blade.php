@extends('layouts.app')

@section('page-content')
    <div class="content container-fluid">
        <x-breadcrumb class="col">
            <x-slot name="title">{{ __('Balance Sheet') }}</x-slot>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                <li class="breadcrumb-item active">{{ __('Balance Sheet') }}</li>
            </ul>
        </x-breadcrumb>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <!-- Date Filter -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <label>{{ __('As of Date') }}</label>
                                <input type="date" id="as_of_date" class="form-control" value="{{ date('Y-m-d') }}">
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
                                <h3>{{ __('Balance Sheet') }}</h3>
                                <p id="as_of_date_display" class="text-muted"></p>
                            </div>

                            <div class="row">
                                <!-- ASSETS Column -->
                                <div class="col-md-6">
                                    <h5 class="bg-light p-2">{{ __('ASSETS') }}</h5>
                                    
                                    <h6 class="mt-3">{{ __('Current Assets') }}</h6>
                                    <table class="table table-sm">
                                        <tbody id="current_assets"></tbody>
                                        <tfoot class="fw-bold border-top">
                                            <tr>
                                                <td>{{ __('Total Current Assets') }}</td>
                                                <td class="text-end" id="total_current_assets">0.00</td>
                                            </tr>
                                        </tfoot>
                                    </table>

                                    <h6 class="mt-3">{{ __('Fixed Assets') }}</h6>
                                    <table class="table table-sm">
                                        <tbody id="fixed_assets"></tbody>
                                        <tfoot class="fw-bold border-top">
                                            <tr>
                                                <td>{{ __('Total Fixed Assets') }}</td>
                                                <td class="text-end" id="total_fixed_assets">0.00</td>
                                            </tr>
                                        </tfoot>
                                    </table>

                                    <table class="table table-sm mt-3">
                                        <tfoot class="fw-bold bg-light">
                                            <tr>
                                                <td>{{ __('TOTAL ASSETS') }}</td>
                                                <td class="text-end" id="total_assets">0.00</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>

                                <!-- LIABILITIES & EQUITY Column -->
                                <div class="col-md-6">
                                    <h5 class="bg-light p-2">{{ __('LIABILITIES & EQUITY') }}</h5>
                                    
                                    <h6 class="mt-3">{{ __('Current Liabilities') }}</h6>
                                    <table class="table table-sm">
                                        <tbody id="current_liabilities"></tbody>
                                        <tfoot class="fw-bold border-top">
                                            <tr>
                                                <td>{{ __('Total Current Liabilities') }}</td>
                                                <td class="text-end" id="total_current_liabilities">0.00</td>
                                            </tr>
                                        </tfoot>
                                    </table>

                                    <h6 class="mt-3">{{ __('Long Term Liabilities') }}</h6>
                                    <table class="table table-sm">
                                        <tbody id="long_term_liabilities"></tbody>
                                        <tfoot class="fw-bold border-top">
                                            <tr>
                                                <td>{{ __('Total Long Term Liabilities') }}</td>
                                                <td class="text-end" id="total_long_term_liabilities">0.00</td>
                                            </tr>
                                        </tfoot>
                                    </table>

                                    <h6 class="mt-3">{{ __('Equity') }}</h6>
                                    <table class="table table-sm">
                                        <tbody id="equity"></tbody>
                                        <tfoot class="fw-bold border-top">
                                            <tr>
                                                <td>{{ __('Total Equity') }}</td>
                                                <td class="text-end" id="total_equity">0.00</td>
                                            </tr>
                                        </tfoot>
                                    </table>

                                    <table class="table table-sm mt-3">
                                        <tfoot class="fw-bold bg-light">
                                            <tr>
                                                <td>{{ __('TOTAL LIABILITIES & EQUITY') }}</td>
                                                <td class="text-end" id="total_liabilities_equity">0.00</td>
                                            </tr>
                                        </tfoot>
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
            url: "{{ route('accounting.balance-sheet') }}",
            data: {
                as_of_date: $('#as_of_date').val()
            },
            success: function(response) {
                $('#as_of_date_display').text('As of ' + response.as_of_date_display);
                
                // Current Assets
                let html = '';
                response.current_assets.forEach(acc => {
                    html += `<tr><td>${acc.name}</td><td class="text-end">${acc.amount}</td></tr>`;
                });
                $('#current_assets').html(html);
                $('#total_current_assets').text(response.total_current_assets);
                
                // Fixed Assets
                html = '';
                response.fixed_assets.forEach(acc => {
                    html += `<tr><td>${acc.name}</td><td class="text-end">${acc.amount}</td></tr>`;
                });
                $('#fixed_assets').html(html);
                $('#total_fixed_assets').text(response.total_fixed_assets);
                $('#total_assets').text(response.total_assets);
                
                // Current Liabilities
                html = '';
                response.current_liabilities.forEach(acc => {
                    html += `<tr><td>${acc.name}</td><td class="text-end">${acc.amount}</td></tr>`;
                });
                $('#current_liabilities').html(html);
                $('#total_current_liabilities').text(response.total_current_liabilities);
                
                // Long Term Liabilities
                html = '';
                response.long_term_liabilities.forEach(acc => {
                    html += `<tr><td>${acc.name}</td><td class="text-end">${acc.amount}</td></tr>`;
                });
                $('#long_term_liabilities').html(html);
                $('#total_long_term_liabilities').text(response.total_long_term_liabilities);
                
                // Equity
                html = '';
                response.equity.forEach(acc => {
                    html += `<tr><td>${acc.name}</td><td class="text-end">${acc.amount}</td></tr>`;
                });
                $('#equity').html(html);
                $('#total_equity').text(response.total_equity);
                $('#total_liabilities_equity').text(response.total_liabilities_equity);
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
