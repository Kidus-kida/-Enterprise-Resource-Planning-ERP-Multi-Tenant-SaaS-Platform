@extends('layouts.app')

@section('page-content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <x-breadcrumb class="col">
            <x-slot name="title">{{ __('Sale Details') }}</x-slot>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('sales.index') }}">{{ __('Sales') }}</a></li>
                <li class="breadcrumb-item active">{{ __('View Sale') }}</li>
            </ul>
        </x-breadcrumb>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <h6><strong>{{ __('Customer') }}:</strong></h6>
                                <p>{{ $sale->contact->name ?? '' }}<br>
                                   {{ $sale->contact->mobile ?? '' }}<br>
                                   {{ $sale->contact->email ?? '' }}</p>
                            </div>
                            <div class="col-md-4">
                                <h6><strong>{{ __('Invoice Info') }}:</strong></h6>
                                <p><strong>{{ __('Invoice No') }}:</strong> {{ $sale->invoice_no }}<br>
                                   <strong>{{ __('Date') }}:</strong> {{ $sale->transaction_date->format('Y-m-d H:i') }}<br>
                                   <strong>{{ __('Status') }}:</strong> <span class="badge bg-primary">{{ ucfirst($sale->status) }}</span></p>
                            </div>
                            <div class="col-md-4">
                                <h6><strong>{{ __('Location') }}:</strong></h6>
                                <p>{{ $sale->location->name ?? '' }}</p>
                            </div>
                        </div>

                        <div class="table-responsive mt-4">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr class="bg-light">
                                        <th>#</th>
                                        <th>{{ __('Product') }}</th>
                                        <th>{{ __('Quantity') }}</th>
                                        <th>{{ __('Unit Price') }}</th>
                                        <th>{{ __('Discount') }}</th>
                                        <th>{{ __('Tax') }}</th>
                                        <th>{{ __('Line Total') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($sale->sell_lines as $line)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                {{ $line->product->name ?? '' }}
                                                @if($line->variations->name != 'DUMMY')
                                                    - {{ $line->variations->name ?? '' }}
                                                @endif
                                            </td>
                                            <td>{{ number_format($line->quantity, 2) }}</td>
                                            <td>{{ number_format($line->unit_price, 2) }}</td>
                                            <td>{{ number_format($line->line_discount_amount, 2) }} {{ $line->line_discount_type == 'percentage' ? '%' : '' }}</td>
                                            <td>{{ number_format($line->item_tax, 2) }}</td>
                                            <td>{{ number_format($line->unit_price_inc_tax * $line->quantity, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-6 offset-md-6">
                                <table class="table table-bordered">
                                    <tr>
                                        <th>{{ __('Total Before Tax') }}</th>
                                        <td class="text-end">{{ number_format($sale->total_before_tax, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Tax Amount') }}</th>
                                        <td class="text-end">{{ number_format($sale->tax_amount, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Discount Amount') }}</th>
                                        <td class="text-end">{{ number_format($sale->discount_amount, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Shipping') }}</th>
                                        <td class="text-end">{{ number_format($sale->shipping_charges, 2) }}</td>
                                    </tr>
                                    <tr class="table-primary">
                                        <th><strong>{{ __('Final Total') }}</strong></th>
                                        <td class="text-end"><strong>{{ number_format($sale->final_total, 2) }}</strong></td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div class="mt-4">
                            <h5>{{ __('Payments') }}</h5>
                            <table class="table table-bordered">
                                <thead>
                                    <tr class="bg-light">
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Method') }}</th>
                                        <th>{{ __('Amount') }}</th>
                                        <th>{{ __('Note') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($sale->payment_lines as $payment)
                                        <tr>
                                            <td>{{ $payment->paid_on->format('Y-m-d') }}</td>
                                            <td>{{ ucfirst($payment->method) }}</td>
                                            <td>{{ number_format($payment->amount, 2) }}</td>
                                            <td>{{ $payment->note }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center">{{ __('No payments found') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
