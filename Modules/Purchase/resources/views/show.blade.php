@extends('layouts.app')

@section('page-content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <x-breadcrumb class="col">
            <x-slot name="title">{{ __('View Purchase') }}</x-slot>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('purchase.index') }}">{{ __('Purchase') }}</a></li>
                <li class="breadcrumb-item active">{{ __('View') }}</li>
            </ul>
        </x-breadcrumb>
        
        <div class="card">
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-sm-4">
                        <h5 class="mb-3">{{ __('Supplier Details') }}:</h5>
                        <h6 class="text-dark mb-1">{{ $transaction->contact->name ?? '' }}</h6>
                        <div class="text-secondary">{{ $transaction->contact->supplier_business_name ?? '' }}</div>
                        <div class="text-secondary">{{ $transaction->contact->address_line_1 ?? '' }}</div>
                        <div class="text-secondary">{{ $transaction->contact->city ?? '' }}</div>
                        <div class="text-secondary">{{ __('Tax No') }}: {{ $transaction->contact->tax_number ?? '' }}</div>
                        <div class="text-secondary">{{ __('Mobile') }}: {{ $transaction->contact->mobile ?? '' }}</div>
                        <div class="text-secondary">{{ __('Email') }}: {{ $transaction->contact->email ?? '' }}</div>
                    </div>
                    <div class="col-sm-4">
                        <h5 class="mb-3">{{ __('Business Location') }}:</h5>
                         <h6 class="text-dark mb-1">{{ $transaction->location->name ?? '' }}</h6>
                        <div class="text-secondary">{{ $transaction->location->city ?? '' }}</div>
                        <div class="text-secondary">{{ $transaction->location->state ?? '' }}</div>
                         <div class="text-secondary">{{ $transaction->location->zip_code ?? '' }}</div>
                        <div class="text-secondary">{{ $transaction->location->country ?? '' }}</div>
                    </div>
                    <div class="col-sm-4">
                        <h5 class="mb-3">{{ __('Purchase Details') }}:</h5>
                        <div class="text-secondary">
                             <strong>{{ __('Ref No') }}:</strong> {{ $transaction->ref_no }}
                        </div>
                        <div class="text-secondary">
                            <strong>{{ __('Date') }}:</strong> {{ @format_date($transaction->transaction_date) }}
                        </div>
                        <div class="text-secondary">
                            <strong>{{ __('Purchase Status') }}:</strong> 
                            <span class="badge @if($transaction->status == 'received') bg-success @elseif($transaction->status == 'pending') bg-warning @else bg-secondary @endif">
                                {{ ucfirst($transaction->status) }}
                            </span>
                        </div>
                        <div class="text-secondary">
                            <strong>{{ __('Payment Status') }}:</strong>
                            <span class="badge @if($transaction->payment_status == 'paid') bg-success @elseif($transaction->payment_status == 'due') bg-danger @else bg-warning @endif">
                                {{ ucfirst($transaction->payment_status) }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ __('Product') }}</th>
                                <th class="text-end">{{ __('Quantity') }}</th>
                                <th class="text-end">{{ __('Unit Cost (Before Discount)') }}</th>
                                <th class="text-end">{{ __('Discount') }}</th>
                                <th class="text-end">{{ __('Tax') }}</th>
                                <th class="text-end">{{ __('Unit Cost (Inc. Tax)') }}</th>
                                <th class="text-end">{{ __('Line Total') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transaction->purchase_lines as $line)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        {{ $line->product->name ?? '' }} 
                                        @if($line->variations->name != 'DUMMY')
                                             ({{ $line->variations->name ?? '' }})
                                        @endif
                                        <br>
                                        <small class="text-muted">{{ $line->variations->sub_sku ?? '' }}</small>
                                    </td>
                                    <td class="text-end">{{ @format_quantity($line->quantity) }}</td>
                                    <td class="text-end">{{ @num_format($line->pp_without_discount) }}</td>
                                    <td class="text-end">{{ @num_format($line->discount_percent) }}%</td>
                                    <td class="text-end">{{ @num_format($line->item_tax) }}</td>
                                    <td class="text-end">{{ @num_format($line->purchase_price_inc_tax) }}</td>
                                    <td class="text-end">{{ @num_format($line->purchase_price_inc_tax * $line->quantity) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        @if($transaction->additional_notes)
                            <h6>{{ __('Additional Notes') }}:</h6>
                            <p class="text-muted">{{ $transaction->additional_notes }}</p>
                        @endif
                    </div>
                    <div class="col-md-6 text-end">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <tbody>
                                    <tr>
                                        <th>{{ __('Total Before Tax') }}:</th>
                                        <td>{{ @num_format($transaction->total_before_tax) }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Discount') }}:</th>
                                        <td>(-) {{ @num_format($transaction->discount_amount) }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Tax') }}:</th>
                                        <td>(+) {{ @num_format($transaction->tax_amount) }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Shipping Charges') }}:</th>
                                        <td>(+) {{ @num_format($transaction->shipping_charges) }}</td>
                                    </tr>
                                    <tr class="fw-bold fs-5">
                                        <th>{{ __('Grand Total') }}:</th>
                                        <td>{{ @num_format($transaction->final_total) }}</td>
                                    </tr>
                                     <tr>
                                        <th>{{ __('Paid') }}:</th>
                                        <td>{{ @num_format($transaction->payments->sum('amount')) }}</td>
                                    </tr>
                                    <tr class="text-danger fw-bold">
                                        <th>{{ __('Due') }}:</th>
                                        <td>{{ @num_format($transaction->final_total - $transaction->payments->sum('amount')) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                @if($transaction->payments->count() > 0)
                <div class="row">
                    <div class="col-md-12">
                        <h5>{{ __('Payment History') }}</h5>
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Ref No') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Payment Method') }}</th>
                                    <th>{{ __('Note') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transaction->payments as $payment)
                                    <tr>
                                        <td>{{ @format_date($payment->paid_on) }}</td>
                                        <td>{{ $payment->payment_ref_no }}</td>
                                        <td>{{ @num_format($payment->amount) }}</td>
                                        <td>{{ ucfirst($payment->method) }}</td>
                                        <td>{{ $payment->note }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
                
            </div>
            <div class="card-footer text-end">
                <a href="{{ route('purchase.index') }}" class="btn btn-secondary">{{ __('Back') }}</a>
                 <a href="{{ route('purchase.edit', $transaction->id) }}" class="btn btn-primary">{{ __('Edit') }}</a>
                 <button type="button" class="btn btn-info" onclick="window.print()">{{ __('Print') }}</button>
            </div>
        </div>
    </div>
@endsection
