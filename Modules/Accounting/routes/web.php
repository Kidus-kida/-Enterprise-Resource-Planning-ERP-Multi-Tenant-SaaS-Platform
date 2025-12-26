<?php

use Illuminate\Support\Facades\Route;
use Modules\Accounting\Http\Controllers\BudgetsController;
use Modules\Accounting\Http\Controllers\ExpenseBudgetController;
use Modules\Accounting\Http\Controllers\RevenueBudgetController;
use Modules\Accounting\Http\Controllers\BudgetCategoriesController;
use Modules\Accounting\Http\Controllers\AccountController;
use Modules\Accounting\Http\Controllers\AccountReportsController;
use Modules\Accounting\Http\Controllers\JournalController;
use Modules\Accounting\Http\Controllers\FixedAssetController;
use Modules\Accounting\Http\Controllers\PostdatedChequeController;
use Modules\Accounting\Http\Controllers\AccountTypeController;
use Modules\Accounting\Http\Controllers\AccountGroupController;
use Modules\Accounting\Http\Controllers\AccountSettingController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(['prefix' => 'accounting','middleware' => ['auth']], function () {
    
    // Budgets Routes (existing)
    Route::resource('budget-categories', BudgetCategoriesController::class)->except('show')->names('budget.categories');
    Route::resource('budget-expense', ExpenseBudgetController::class)->except('show')->names('budget.expense');
    Route::resource('budget-revenue', RevenueBudgetController::class)->except('show')->names('budget.revenue');
    Route::resource('budgets', BudgetsController::class);
    
    // ====================
    // CHART OF ACCOUNTS
    // ====================
    
    // Main Account Routes
    Route::get('/accounts/import', [AccountController::class, 'import'])->name('accounts.import');
    Route::post('/accounts/import', [AccountController::class, 'postImport'])->name('accounts.post-import');
    Route::get('/accounts/disabled', [AccountController::class, 'disabled'])->name('accounts.disabled');
    Route::resource('/accounts', AccountController::class);
    
    // Account Transactions
    Route::get('/account-transaction/{id}/edit', [AccountController::class, 'editAccountTransaction'])->name('account.transaction.edit');
    Route::put('/account-transaction/{id}', [AccountController::class, 'updateAccountTransaction'])->name('account.transaction.update');
    Route::delete('/account-transaction/{id}', [AccountController::class, 'deleteAccountTransaction'])->name('account.transaction.delete');
    
    // Fund Transfer
    Route::get('/fund-transfer/{id}', [AccountController::class, 'getFundTransfer'])->name('account.fund-transfer');
    Route::post('/fund-transfer', [AccountController::class, 'postFundTransfer'])->name('account.post-fund-transfer');
    
    // Cheque Deposit
    Route::get('/cheque-deposit/{id}', [AccountController::class, 'getChequeDeposit'])->name('account.cheque-deposit');
    Route::post('/cheque-deposit', [AccountController::class, 'postChequeDeposit'])->name('account.post-cheque-deposit');
    
    // Account Helper Routes
    Route::get('/get-account-dropdown', [AccountController::class, 'getAccountDropdown'])->name('account.dropdown');

    // Deposit & Transfers List
    Route::get('/deposit-transfers', [\Modules\Accounting\Http\Controllers\DepositTransferController::class, 'index'])->name('deposit-transfers.index');
    Route::get('/cheques-list', [AccountController::class, 'listCheques'])->name('account.cheques-list');

    // Missing Account Actions matching Old ERP
    Route::get('/deposit/{type}', [AccountController::class, 'getDeposit'])->name('account.deposit');
    Route::post('/deposit', [AccountController::class, 'postDeposit'])->name('account.post-deposit');
    Route::get('/account/{id}/close', [AccountController::class, 'close'])->name('account.close');
    Route::get('/account/{id}/activate', [AccountController::class, 'activate'])->name('account.activate');
    Route::get('/account/{id}/notes', [AccountController::class, 'getNotes'])->name('account.get-notes');
    Route::post('/account/{id}/notes', [AccountController::class, 'postNotes'])->name('account.post-notes');
    Route::get('/account-book/{id}', [AccountController::class, 'accountBook'])->name('accounting.account_book');
    Route::get('/cheque-deposit', [AccountController::class, 'getChequeDeposit'])->name('account.cheque-deposit'); // Generic cheque deposit
    
    // ====================
    // JOURNALS
    // ====================
    Route::get('/journal/get-account-dropdown', [JournalController::class, 'getAccountDropdown'])->name('journal.account-dropdown');
    Route::resource('/journal', JournalController::class);
    
    // ====================
    // FINANCIAL REPORTS
    // ====================
    
    // Income Statement (P&L)
    Route::get('/reports/income-statement', [AccountReportsController::class, 'incomeStatement'])->name('accounting.income-statement');
    Route::get('/reports/profit-loss', [AccountReportsController::class, 'profitLoss'])->name('accounting.profit-loss');
    
    // Balance Sheet
    Route::get('/reports/balance-sheet', [AccountReportsController::class, 'balanceSheet'])->name('accounting.balance-sheet');
    Route::get('/reports/balance-sheet-comparison', [AccountReportsController::class, 'balanceSheetComparison'])->name('accounting.balance-sheet-comparison');
    
    // Trial Balance
    Route::get('/reports/trial-balance', [AccountReportsController::class, 'trialBalance'])->name('accounting.trial-balance');
    Route::get('/reports/trial-balance-cumulative', [AccountReportsController::class, 'trialBalanceCumulative'])->name('accounting.trial-balance-cumulative');
    
    // Cash Flow
    Route::get('/reports/cash-flow', [AccountReportsController::class, 'cashFlow'])->name('accounting.cash-flow');
    
    // Payment Account Report
    Route::get('/reports/payment-account', [AccountReportsController::class, 'paymentAccountReport'])->name('accounting.payment-account-report');
    
    // ====================
    // FIXED ASSETS
    // ====================
    Route::resource('/fixed-asset', FixedAssetController::class);
    
    // ====================
    // POST-DATED CHEQUES
    // ====================
    Route::get('/post-dated-cheques-filters', [PostdatedChequeController::class, 'postDatedFilters'])->name('pdc.filters');
    Route::get('/old-post-dated-cheques-filters', [PostdatedChequeController::class, 'oldpostDatedFilters'])->name('pdc.old-filters');
    Route::get('/old-post-dated-cheques', [PostdatedChequeController::class, 'oldPostDatedCheques'])->name('pdc.old');
    Route::get('/dated-cheques-party-type', [PostdatedChequeController::class, 'partyType'])->name('pdc.party-type');
    Route::resource('/post-dated-cheques', PostdatedChequeController::class);
    
    // ====================
    // ACCOUNT SETTINGS
    // ====================
    Route::resource('/account-settings', AccountSettingController::class);
    
    // ====================
    // ACCOUNT TYPES & GROUPS (Admin)
    // ====================
    Route::resource('/account-types', AccountTypeController::class);
    Route::resource('/account-groups', AccountGroupController::class);
});
