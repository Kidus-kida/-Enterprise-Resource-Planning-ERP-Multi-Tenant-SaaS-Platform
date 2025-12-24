@extends('layouts.app')
@section('title', __('contact.view_contact'))

@section('page-content')
<div class="content container-fluid">
    <!-- Page Header -->
    <x-breadcrumb class="col">
        <x-slot name="title">{{ __('contact.view_contact') }}</x-slot>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ action([\Modules\Contacts\Http\Controllers\ContactController::class, 'index']) }}">{{ __('Contacts') }}</a></li>
            <li class="breadcrumb-item active">{{ $contact->name }}</li>
        </ul>
    </x-breadcrumb>

    <div class="row">
        <div class="col-md-12">
            <div class="card card-primary card-outline card-tabs">
                <div class="card-header p-0 pt-1 border-bottom-0">
                    <ul class="nav nav-tabs pull-right" id="custom-tabs-three-tab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link @if($view_type == 'contact_info') active @endif" id="contact-info-tab" data-bs-toggle="tab" href="#contact_info" role="tab" aria-controls="contact_info" aria-selected="true">
                                <i class="fa fa-user"></i> @lang('contact.contact_info', ['contact' => __('contact.contact') ])
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link @if($view_type == 'ledger') active @endif" id="ledger-tab-link" data-bs-toggle="tab" href="#ledger" role="tab" aria-controls="ledger" aria-selected="false">
                                <i class="fa fa-anchor"></i> @lang('lang_v1.ledger')
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link @if($view_type == 'payments') active @endif" id="payments-tab-link" data-bs-toggle="tab" href="#payments" role="tab" aria-controls="payments" aria-selected="false">
                                <i class="fa fa-money"></i> @lang('lang_v1.payment')
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="custom-tabs-three-tabContent">
                        <div class="tab-pane fade @if($view_type == 'contact_info') show active @endif" id="contact_info" role="tabpanel" aria-labelledby="contact-info-tab">
                            <div class="row">
                                <div class="col-md-4">
                                    <strong>{{ $contact->name }}</strong><br>
                                    @if($contact->supplier_business_name)
                                        {{ $contact->supplier_business_name }}<br>
                                    @endif
                                    <strong><i class="fa fa-map-marker"></i> @lang('business.address')</strong>
                                    <p class="text-muted">
                                        {{ $contact->landmark }} {{ $contact->city }} {{ $contact->state }} {{ $contact->country }}
                                    </p>
                                </div>
                                <div class="col-md-4">
                                    <strong><i class="fa fa-mobile"></i> @lang('contact.mobile')</strong>
                                    <p class="text-muted">{{ $contact->mobile }}</p>
                                    <strong><i class="fa fa-envelope"></i> @lang('business.email')</strong>
                                    <p class="text-muted">{{ $contact->email }}</p>
                                </div>
                                <div class="col-md-4">
                                    @if($contact->type == 'supplier' || $contact->type == 'both')
                                        <strong>@lang('contact.total_purchase')</strong>
                                        <p class="text-muted">{{ @num_format($contact->total_purchase) }}</p>
                                        <strong>@lang('contact.total_paid')</strong>
                                        <p class="text-muted">{{ @num_format($contact->purchase_paid) }}</p>
                                    @endif
                                    @if($contact->type == 'customer' || $contact->type == 'both')
                                        <strong>@lang('contact.total_sale')</strong>
                                        <p class="text-muted">{{ @num_format($contact->total_invoice) }}</p>
                                        <strong>@lang('contact.total_paid')</strong>
                                        <p class="text-muted">{{ @num_format($contact->invoice_received) }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade @if($view_type == 'ledger') show active @endif" id="ledger" role="tabpanel" aria-labelledby="ledger-tab-link">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        {!! Form::label('ledger_date_range', __('report.date_range') . ':') !!}
                                        {!! Form::text('ledger_date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'readonly', 'id' => 'ledger_date_range']); !!}
                                    </div>
                                    <div id="contact_ledger_div"></div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade @if($view_type == 'payments') show active @endif" id="payments" role="tabpanel" aria-labelledby="payments-tab-link">
                            <div id="contact_payment_div"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('page-scripts')
<script type="text/javascript">
$(document).ready( function(){
    if ($('#ledger_date_range').length == 1) {
        $('#ledger_date_range').daterangepicker(dateRangeSettings, function(start, end) {
            $('#ledger_date_range').val(
                start.format(moment_date_format) + ' - ' + end.format(moment_date_format)
            );
            get_contact_ledger();
        });
    }

    get_contact_ledger();
    get_contact_payment();

    function get_contact_ledger() {
        var start_date = '';
        var end_date = '';
        if($('#ledger_date_range').val()) {
            start_date = $('#ledger_date_range').data('daterangepicker').startDate.format('YYYY-MM-DD');
            end_date = $('#ledger_date_range').data('daterangepicker').endDate.format('YYYY-MM-DD');
        }
        $.ajax({
            url: '/contacts/ledger?contact_id={{$contact->id}}&start_date=' + start_date + '&end_date=' + end_date,
            dataType: 'html',
            success: function(result) {
                $('#contact_ledger_div').html(result);
            },
        });
    }

    function get_contact_payment() {
        $.ajax({
            url: '/contacts/payments?contact_id={{$contact->id}}',
            dataType: 'html',
            success: function(result) {
                $('#contact_payment_div').html(result);
            },
        });
    }
});
</script>
@endpush
