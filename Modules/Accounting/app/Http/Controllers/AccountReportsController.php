<?php

namespace Modules\Accounting\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Accounting\Models\Account;
use Modules\Accounting\Models\AccountType;
use Modules\Accounting\Models\AccountTransaction;
use Illuminate\Support\Facades\DB;

class AccountReportsController extends Controller
{
    /**
     * Income Statement Report
     */
    public function incomeStatement(Request $request)
    {
        $business_id = session()->get('user.business_id');
        $from_date = $request->from_date ?? date('Y-m-01');
        $to_date = $request->to_date ?? date('Y-m-d');

        if ($request->ajax()) {
            // Get Income Accounts
            $income_type = AccountType::where('business_id', $business_id)
                ->where('name', 'Income')
                ->first();
            
            $income_accounts = [];
            $total_income = 0;
            
            if ($income_type) {
                $accounts = Account::where('business_id', $business_id)
                    ->where('account_type_id', $income_type->id)
                    ->get();
                
                foreach ($accounts as $account) {
                    $balance = Account::getAccountBalance($account->id, $from_date, $to_date);
                    $income_accounts[] = [
                        'name' => $account->name,
                        'amount' => number_format(abs($balance), 2)
                    ];
                    $total_income += abs($balance);
                }
            }

            // Get Expense Accounts
            $expense_type = AccountType::where('business_id', $business_id)
                ->where('name', 'Expenses')
                ->first();
            
            $expense_accounts = [];
            $total_expenses = 0;
            
            if ($expense_type) {
                $accounts = Account::where('business_id', $business_id)
                    ->where('account_type_id', $expense_type->id)
                    ->get();
                
                foreach ($accounts as $account) {
                    $balance = Account::getAccountBalance($account->id, $from_date, $to_date);
                    $expense_accounts[] = [
                        'name' => $account->name,
                        'amount' => number_format(abs($balance), 2)
                    ];
                    $total_expenses += abs($balance);
                }
            }

            $net_income = $total_income - $total_expenses;

            return response()->json([
                'period' => date('M d, Y', strtotime($from_date)) . ' - ' . date('M d, Y', strtotime($to_date)),
                'income_accounts' => $income_accounts,
                'expense_accounts' => $expense_accounts,
                'total_income' => number_format($total_income, 2),
                'total_expenses' => number_format($total_expenses, 2),
                'net_income' => number_format($net_income, 2),
                'net_income_raw' => $net_income
            ]);
        }

        return view('accounting::reports.income_statement');
    }

    /**
     * Balance Sheet Report
     */
    public function balanceSheet(Request $request)
    {
        $business_id = session()->get('user.business_id');
        $as_of_date = $request->as_of_date ?? date('Y-m-d');

        if ($request->ajax()) {
            // Assets
            $current_assets = $this->getAccountsByType($business_id, 'Current Assets', null, $as_of_date);
            $fixed_assets = $this->getAccountsByType($business_id, 'Fixed Assets', null, $as_of_date);
            
            $total_current_assets = collect($current_assets)->sum('amount_raw');
            $total_fixed_assets = collect($fixed_assets)->sum('amount_raw');
            $total_assets = $total_current_assets + $total_fixed_assets;

            // Liabilities
            $current_liabilities = $this->getAccountsByType($business_id, 'Current Liabilities', null, $as_of_date);
            $long_term_liabilities = $this->getAccountsByType($business_id, 'Long Term Liabilities', null, $as_of_date);
            
            $total_current_liabilities = collect($current_liabilities)->sum('amount_raw');
            $total_long_term_liabilities = collect($long_term_liabilities)->sum('amount_raw');

            // Equity
            $equity = $this->getAccountsByType($business_id, 'Equity', null, $as_of_date);
            $total_equity = collect($equity)->sum('amount_raw');
            
            $total_liabilities_equity = $total_current_liabilities + $total_long_term_liabilities + $total_equity;

            return response()->json([
                'as_of_date_display' => date('M d, Y', strtotime($as_of_date)),
                'current_assets' => $current_assets,
                'fixed_assets' => $fixed_assets,
                'total_current_assets' => number_format($total_current_assets, 2),
                'total_fixed_assets' => number_format($total_fixed_assets, 2),
                'total_assets' => number_format($total_assets, 2),
                'current_liabilities' => $current_liabilities,
                'long_term_liabilities' => $long_term_liabilities,
                'equity' => $equity,
                'total_current_liabilities' => number_format($total_current_liabilities, 2),
                'total_long_term_liabilities' => number_format($total_long_term_liabilities, 2),
                'total_equity' => number_format($total_equity, 2),
                'total_liabilities_equity' => number_format($total_liabilities_equity, 2),
            ]);
        }

        return view('accounting::reports.balance_sheet');
    }

    /**
     * Trial Balance Report
     */
    public function trialBalance(Request $request)
    {
        $business_id = session()->get('user.business_id');
        $from_date = $request->from_date ?? date('Y-m-01');
        $to_date = $request->to_date ?? date('Y-m-d');

        if ($request->ajax()) {
            $accounts = Account::where('business_id', $business_id)
                ->where('is_closed', 0)
                ->get();

            $trial_balance = [];
            $total_debit = 0;
            $total_credit = 0;

            foreach ($accounts as $account) {
                $balance = Account::getAccountBalance($account->id, $from_date, $to_date);
                
                $debit = $balance >= 0 ? abs($balance) : 0;
                $credit = $balance < 0 ? abs($balance) : 0;

                $total_debit += $debit;
                $total_credit += $credit;

                $trial_balance[] = [
                    'account_number' => $account->account_number ?? 'N/A',
                    'name' => $account->name,
                    'debit' => number_format($debit, 2),
                    'credit' => number_format($credit, 2),
                ];
            }

            $difference = $total_debit - $total_credit;

            return response()->json([
                'period' => date('M d, Y', strtotime($from_date)) . ' - ' . date('M d, Y', strtotime($to_date)),
                'accounts' => $trial_balance,
                'total_debit' => number_format($total_debit, 2),
                'total_credit' => number_format($total_credit, 2),
                'difference' => number_format($difference, 2),
                'difference_raw' => $difference
            ]);
        }

        return view('accounting::reports.trial_balance');
    }

    /**
     * Cash Flow Report
     */
    public function cashFlow(Request $request)
    {
        // Placeholder for cash flow report
        return view('accounting::reports.cash_flow');
    }

    /**
     * Payment Account Report
     */
    public function paymentAccountReport(Request $request)
    {
        // Placeholder for payment account report
        return view('accounting::reports.payment_account');
    }

    /**
     * Helper method to get accounts by type
     */
    private function getAccountsByType($business_id, $type_name, $from_date = null, $to_date = null)
    {
        $account_type = AccountType::where('business_id', $business_id)
            ->where('name', $type_name)
            ->first();
        
        if (!$account_type) {
            return [];
        }

        $accounts = Account::where('business_id', $business_id)
            ->where('account_type_id', $account_type->id)
            ->get();
        
        $result = [];
        foreach ($accounts as $account) {
            $balance = Account::getAccountBalance($account->id, $from_date, $to_date);
            $result[] = [
                'name' => $account->name,
                'amount' => number_format(abs($balance), 2),
                'amount_raw' => abs($balance)
            ];
        }

        return $result;
    }

    /**
     * Profit Loss Report (same as Income Statement)
     */
    public function profitLoss(Request $request)
    {
        return $this->incomeStatement($request);
    }

    /**
     * Balance Sheet Comparison
     */
    public function balanceSheetComparison(Request $request)
    {
        // Placeholder - can be implemented later
        return view('accounting::reports.balance_sheet');
    }

    /**
     * Trial Balance Cumulative
     */
    public function trialBalanceCumulative(Request $request)
    {
        // Placeholder - can be implemented later
        return $this->trialBalance($request);
    }
}
