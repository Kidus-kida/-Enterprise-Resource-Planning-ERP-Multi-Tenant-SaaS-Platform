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
                <li class="breadcrumb-item active">
                    {{ $account->name }}
                </li>
            </ul>
            <x-slot name="right">
                <div class="col-auto float-end ms-auto">
                    <a href="{{ route('accounting.accounts.index') }}" class="btn btn-outline-primary">
                        <i class="fa-solid fa-arrow-left"></i> {{ __('Back to List') }}
                    </a>
                    <a href="javascript:void(0)" data-url="{{ route('accounting.accounts.edit', $account->id) }}" class="btn btn-primary"
                        data-ajax-modal="true" data-size="lg" data-title="Edit Account">
                        <i class="fa-solid fa-pencil"></i> {{ __('Edit') }}
                    </a>
                </div>
            </x-slot>
        </x-breadcrumb>
        <!-- /Page Header -->

        <!-- Account Details -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">{{ __('Account Information') }}</h4>
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tbody>
                                        <tr>
                                            <td class="fw-bold">{{ __('Account Name') }}:</td>
                                            <td>{{ $account->name }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">{{ __('Account Number') }}:</td>
                                            <td>{{ $account->account_number }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">{{ __('Account Type') }}:</td>
                                            <td>{{ $account->accountType ? $account->accountType->name : 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">{{ __('Account Group') }}:</td>
                                            <td>{{ $account->accountGroup ? $account->accountGroup->name : 'N/A' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tbody>
                                        <tr>
                                            <td class="fw-bold">{{ __('Parent Account') }}:</td>
                                            <td>{{ $account->parentAccount ? $account->parentAccount->name : 'None' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">{{ __('Current Balance') }}:</td>
                                            <td class="text-info fw-bold fs-5">{{ number_format($balance, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">{{ __('Main Account') }}:</td>
                                            <td>
                                                @if($account->is_main_account)
                                                    <span class="badge bg-success">Yes</span>
                                                @else
                                                    <span class="badge bg-secondary">No</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">{{ __('Status') }}:</td>
                                            <td>
                                                @if($account->is_closed)
                                                    <span class="badge bg-danger">Closed</span>
                                                @else
                                                    <span class="badge bg-success">Active</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        @if($account->note)
                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <p class="fw-bold">{{ __('Note') }}:</p>
                                    <p>{{ $account->note }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <!-- /Account Details -->

        <!-- Recent Transactions -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">{{ __('Recent Transactions') }}</h4>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Type') }}</th>
                                        <th>{{ __('Description') }}</th>
                                        <th class="text-end">{{ __('Debit') }}</th>
                                        <th class="text-end">{{ __('Credit') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($transactions as $transaction)
                                        <tr>
                                            <td>{{ $transaction->operation_date->format('d M Y') }}</td>
                                            <td>
                                                @if($transaction->type == 'debit')
                                                    <span class="badge bg-success">Debit</span>
                                                @else
                                                    <span class="badge bg-danger">Credit</span>
                                                @endif
                                            </td>
                                            <td>{{ $transaction->description ?? $transaction->note }}</td>
                                            <td class="text-end">
                                                @if($transaction->type == 'debit')
                                                    {{ number_format($transaction->amount, 2) }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                @if($transaction->type == 'credit')
                                                    {{ number_format($transaction->amount, 2) }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center">{{ __('No transactions found') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            {{ $transactions->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Recent Transactions -->

    </div>
@endsection
