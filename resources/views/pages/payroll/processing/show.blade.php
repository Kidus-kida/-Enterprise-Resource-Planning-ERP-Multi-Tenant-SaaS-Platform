@extends('layouts.app')

@section('page-content')
    <div class="content container-fluid">

        
        <style>
            .underline-menu-icon {
                letter-spacing: 3px;
                font-size: 22px;
                line-height: 10px;
                font-weight: bold;
            }

            .dropdown-item.approve {
                background: #28a745 !important; 
                color: white !important;
                border-radius: 5px;
                margin: 5px;
            }

            .dropdown-item.excel {
                background: #fd7e14 !important; 
                color: white !important;
                border-radius: 5px;
                margin: 5px;
            }

            .dropdown-item.print {
                background: #6c757d !important;
                color: white !important;
                border-radius: 5px;
                margin: 5px;
            }
        </style>

    
        <x-breadcrumb>
            <x-slot name="title">{{ __('Payroll Details') }}</x-slot>
            <ul class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('payroll.processing.index') }}">{{ __('Payroll Processing') }}</a>
                </li>
                <li class="breadcrumb-item active">{{ $batch->batch_number }}</li>
            </ul>
            <x-slot name="right">
                <div class="col-auto float-end ms-auto">
                    <div class="dropdown">
                        
                       <button class="btn" 
        style="background: rgb(229, 33, 76); 
               color: white; 
               font-size: 30px; /* bigger ☰ */
               width: 40px;      /* square button */
               height: 40px;     /* square button */
               display: flex; 
               align-items: center; 
               justify-content: center; 
               border-radius: 10px;" 
        type="button" 
        data-bs-toggle="dropdown">
    ☰
</button><ul class="dropdown-menu dropdown-menu-end">

                    @if($batch->status == 'draft')<li>                        <form action="{{ route('payroll.processing.approve', $batch->id) }}" method="POST">                           @csrf                            <button type="submit" class="dropdown-item approve" onclick="return confirm('Are you sure you want to approve this payroll? This action cannot be undone.')">
                                Approve Payroll
                            </button>                        </form>
                     </li>@endif<li> <a class="dropdown-item excel"href="{{ route('payroll.processing.export', $batch->id) }}">Export to Excel
                           </a></li><li><button class="dropdown-item print" onclick="window.print()">Print</button></li></ul></div>
                </div>
            </x-slot>
        </x-breadcrumb>
        <!-- /Page Header -->

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-sm-6 m-b-20">
                                <img src="{{ appLogo() }}" class="inv-logo" alt="">
                                <ul class="list-unstyled mb-0">
                                    <li>{{ Theme('name') }}</li>
                                    <li>{{ format_date($batch->pay_date) }}</li>
                                </ul>
                            </div>
                            <div class="col-sm-6 m-b-20">
                                <div class="invoice-details">
                                    <h3 class="text-uppercase">{{ __('Payroll') }} #{{ $batch->batch_number }}</h3>
                                    <ul class="list-unstyled">
                                        <li>{{ __('Period:') }} <span>{{ format_date($batch->period_start) }} - {{ format_date($batch->period_end) }}</span></li>
                                        <li>{{ __('Status:') }} <span class="text-uppercase">{{ $batch->status }}</span></li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('Employee') }}</th>
                                        <th class="text-end">{{ __('Basic Salary') }}</th>
                                        <th class="text-end">{{ __('Working Days') }}</th>
                                        <th class="text-end">{{ __('Allowances') }}</th>
                                        <th class="text-end">{{ __('Overtime') }}</th>
                                        <th class="text-end">{{ __('Gross Salary') }}</th>
                                        <th class="text-end">{{ __('Taxable Income') }}</th>
                                        <th class="text-end">{{ __('Income Tax') }}</th>
                                        <th class="text-end">{{ __('Pension (7%)') }}</th>
                                        <th class="text-end">{{ __('Other Ded.') }}</th>
                                        <th class="text-end">{{ __('Net Salary') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($batch->details as $index => $detail)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <h2 class="table-avatar">
                                                    <a href="#" class="avatar avatar-xs"><img src="{{ !empty($detail->employee->user->avatar) ? uploadedAsset($detail->employee->user->avatar,'users') : asset('images/user.jpg') }}" alt=""></a>
                                                    <a href="#">{{ $detail->employee->user->fullname }}</a>
                                                </h2>
                                            </td>
                                            <td class="text-end">{{ number_format($detail->basic_salary, 2) }}</td>
                                            <td class="text-end">{{ number_format($detail->working_days, 2) }}</td>
                                            <td class="text-end">
                                                {{ number_format($detail->taxable_allowances + $detail->non_taxable_allowances, 2) }}
                                                @if($detail->non_taxable_allowances > 0)
                                                    <i class="fa fa-info-circle text-muted" data-bs-toggle="tooltip" title="Non-taxable: {{ number_format($detail->non_taxable_allowances, 2) }}"></i>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                {{ number_format($detail->overtime_pay, 2) }}
                                                @if($detail->overtime_pay > 0)
                                                    <i class="fa fa-clock-o text-muted" data-bs-toggle="tooltip" title="Reg: {{ $detail->overtime_regular_hours }}h, Sun: {{ $detail->overtime_sunday_hours }}h, Hol: {{ $detail->overtime_holiday_hours }}h"></i>
                                                @endif
                                            </td>
                                            <td class="text-end fw-bold">{{ number_format($detail->gross_salary, 2) }}</td>
                                            <td class="text-end">{{ number_format($detail->taxable_income, 2) }}</td>
                                            <td class="text-end text-danger">{{ number_format($detail->income_tax, 2) }}</td>
                                            <td class="text-end text-danger">{{ number_format($detail->pension_employee, 2) }}</td>
                                            <td class="text-end text-danger">{{ number_format($detail->other_deductions, 2) }}</td>
                                            <td class="text-end fw-bold text-success">{{ number_format($detail->net_salary, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="fw-bold bg-light">
                                        <td colspan="2" class="text-end">{{ __('Totals:') }}</td>
                                        <td class="text-end">{{ number_format($batch->details->sum('basic_salary'), 2) }}</td>
                                        <td class="text-end">{{ number_format($batch->details->sum('working_days'), 2) }}</td>
                                        <td class="text-end">{{ number_format($batch->details->sum('taxable_allowances') + $batch->details->sum('non_taxable_allowances'), 2) }}</td>
                                        <td class="text-end">{{ number_format($batch->details->sum('overtime_pay'), 2) }}</td>
                                        <td class="text-end">{{ number_format($batch->total_gross, 2) }}</td>
                                        <td class="text-end">{{ number_format($batch->details->sum('taxable_income'), 2) }}</td>
                                        <td class="text-end text-danger">{{ number_format($batch->details->sum('income_tax'), 2) }}</td>
                                        <td class="text-end text-danger">{{ number_format($batch->details->sum('pension_employee'), 2) }}</td>
                                        <td class="text-end text-danger">{{ number_format($batch->details->sum('other_deductions'), 2) }}</td>
                                        <td class="text-end text-success">{{ number_format($batch->total_net, 2) }}</td>
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
