@extends('layouts.app')
@section('page-content')
<div class="content container-fluid">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title">Duty Calculator</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('logistics.dashboard') }}">Logistics</a></li>
                    <li class="breadcrumb-item active">Calculator</li>
                </ul>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Calculator Form -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Calculate Import Duties</h4>
                </div>
                <div class="card-body">
                    <form id="calculator-form">
                        <div class="form-group">
                            <label>HS Code Category</label>
                            <select class="select form-control" name="hs_code_id" id="hs_code_id">
                                @foreach($hsCodes as $code)
                                    <option value="{{ $code->id }}">{{ $code->code }} - {{ \Illuminate\Support\Str::limit($code->description, 50) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>CIF Value (USD)</label>
                            <input type="number" class="form-control" name="cif_value_usd" id="cif_value_usd" required>
                        </div>
                        <div class="form-group">
                            <label>Exchange Rate (ETB/USD)</label>
                            <input type="number" class="form-control" name="exchange_rate" id="exchange_rate" value="120.00" required>
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-primary" id="calculate-btn">Calculate</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- Results -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Estimated Duties</h4>
                </div>
                <div class="card-body" id="calculator-results">
                    <p class="text-muted text-center" id="empty-state">Enter details to see estimation</p>
                    <div id="results-content" style="display:none;">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                CIF Value (ETB)
                                <span class="badge bg-primary rounded-pill" id="res-cif">0</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Import Duty
                                <span class="badge bg-secondary rounded-pill" id="res-duty">0</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Excise Tax
                                <span class="badge bg-secondary rounded-pill" id="res-excise">0</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                VAT
                                <span class="badge bg-secondary rounded-pill" id="res-vat">0</span>
                            </li>
                             <li class="list-group-item d-flex justify-content-between align-items-center">
                                Surtax
                                <span class="badge bg-secondary rounded-pill" id="res-surtax">0</span>
                            </li>
                             <li class="list-group-item d-flex justify-content-between align-items-center">
                                Withholding Tax
                                <span class="badge bg-secondary rounded-pill" id="res-wht">0</span>
                            </li>
                             <li class="list-group-item d-flex justify-content-between align-items-center">
                                Customs Service Fee
                                <span class="badge bg-secondary rounded-pill" id="res-fee">0</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center font-weight-bold bg-light">
                                <strong>TOTAL DUTIES</strong>
                                <strong class="text-danger" id="res-total">0</strong>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('page-scripts')
<script type="module">
    $(document).ready(function() {
        $('#calculate-btn').on('click', function() {
            var formData = {
                hs_code_id: $('#hs_code_id').val(),
                cif_value_usd: $('#cif_value_usd').val(),
                exchange_rate: $('#exchange_rate').val(),
                _token: "{{ csrf_token() }}"
            };
            
            $.ajax({
                url: "{{ route('logistics.calculator.calculate') }}",
                type: "POST",
                data: formData,
                success: function(response) {
                    $('#empty-state').hide();
                    $('#results-content').show();
                    
                    $('#res-cif').text(new Intl.NumberFormat('en-ET', { style: 'currency', currency: 'ETB' }).format(response.cif_value_etb));
                    $('#res-duty').text(new Intl.NumberFormat('en-ET', { style: 'currency', currency: 'ETB' }).format(response.import_duty));
                    $('#res-excise').text(new Intl.NumberFormat('en-ET', { style: 'currency', currency: 'ETB' }).format(response.excise_tax));
                    $('#res-vat').text(new Intl.NumberFormat('en-ET', { style: 'currency', currency: 'ETB' }).format(response.vat));
                    $('#res-surtax').text(new Intl.NumberFormat('en-ET', { style: 'currency', currency: 'ETB' }).format(response.surtax));
                    $('#res-wht').text(new Intl.NumberFormat('en-ET', { style: 'currency', currency: 'ETB' }).format(response.withholding));
                    $('#res-fee').text(new Intl.NumberFormat('en-ET', { style: 'currency', currency: 'ETB' }).format(response.customs_service_fee));
                    $('#res-total').text(new Intl.NumberFormat('en-ET', { style: 'currency', currency: 'ETB' }).format(response.total_duties));
                },
                error: function(xhr) {
                    alert('Error calculating duties. Please check inputs.');
                }
            });
        });
    });
</script>
@endpush
@endsection
