<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_batch_id',
        'employee_detail_id',
        'basic_salary',
        'taxable_allowances',
        'non_taxable_allowances',
        'overtime_regular_hours',
        'overtime_sunday_hours',
        'overtime_holiday_hours',
        'overtime_pay',
        'gross_salary',
        'taxable_income',
        'income_tax',
        'pension_employee',
        'pension_employer',
        'other_deductions',
        'total_deductions',
        'net_salary',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'taxable_allowances' => 'decimal:2',
        'non_taxable_allowances' => 'decimal:2',
        'overtime_regular_hours' => 'decimal:2',
        'overtime_sunday_hours' => 'decimal:2',
        'overtime_holiday_hours' => 'decimal:2',
        'overtime_pay' => 'decimal:2',
        'gross_salary' => 'decimal:2',
        'taxable_income' => 'decimal:2',
        'income_tax' => 'decimal:2',
        'pension_employee' => 'decimal:2',
        'pension_employer' => 'decimal:2',
        'other_deductions' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'net_salary' => 'decimal:2',
    ];

    /**
     * Get the batch
     */
    public function batch()
    {
        return $this->belongsTo(PayrollBatch::class, 'payroll_batch_id');
    }

    /**
     * Get the employee
     */
    public function employee()
    {
        return $this->belongsTo(EmployeeDetail::class, 'employee_detail_id');
    }

    /**
     * Calculate payroll for an employee
     */
    public static function calculatePayroll($employeeId, $overtimeData = [])
    {
        $employee = EmployeeDetail::with(['user', 'allowances', 'deductions', 'salaryDetails'])->find($employeeId);
        
        if (!$employee || !$employee->salaryDetails) {
            return null;
        }

        // Get basic salary
        $basicSalary = $employee->salaryDetails->base_salary ?? 0;

        // Get allowances
        $totalAllowances = $employee->allowances->sum('amount');
        
        // Determine employee type and threshold
        $designation = $employee->user->designation ?? '';
        $isManagerial = stripos($designation, 'manager') !== false || stripos($designation, 'director') !== false;
        
        $threshold = $isManagerial 
            ? PayrollSetting::get('taxable_allowance_managerial', 2200)
            : PayrollSetting::get('taxable_allowance_regular', 600);

        $nonTaxableAllowances = min($totalAllowances, $threshold);
        $taxableAllowances = max(0, $totalAllowances - $threshold);

        // Calculate overtime
        $workingDays = PayrollSetting::get('working_days_per_week', 5) * 4; // Approximate monthly
        $workingHours = PayrollSetting::get('working_hours_per_day', 8);
        $hourlyRate = $basicSalary / ($workingDays * $workingHours);

        $regularHours = $overtimeData['regular'] ?? 0;
        $sundayHours = $overtimeData['sunday'] ?? 0;
        $holidayHours = $overtimeData['holiday'] ?? 0;

        $regularRate = PayrollSetting::get('overtime_regular_rate', 1.5);
        $sundayRate = PayrollSetting::get('overtime_sunday_rate', 2.0);
        $holidayRate = PayrollSetting::get('overtime_holiday_rate', 2.5);

        $overtimePay = ($regularHours * $hourlyRate * $regularRate) +
                       ($sundayHours * $hourlyRate * $sundayRate) +
                       ($holidayHours * $hourlyRate * $holidayRate);

        // Calculate gross salary
        $grossSalary = $basicSalary + $totalAllowances + $overtimePay;

        // Calculate taxable income
        $taxableIncome = $basicSalary + $taxableAllowances + $overtimePay;

        // Calculate income tax
        $incomeTax = PayrollTaxBracket::calculateTax($taxableIncome);

        // Calculate pension
        $pensionEmployeePercent = PayrollSetting::get('pension_employee_percent', 7);
        $pensionEmployerPercent = PayrollSetting::get('pension_employer_percent', 11);
        
        $pensionEmployee = $basicSalary * ($pensionEmployeePercent / 100);
        $pensionEmployer = $basicSalary * ($pensionEmployerPercent / 100);

        // Other deductions
        $otherDeductions = $employee->deductions->sum('amount');

        // Total deductions
        $totalDeductions = $incomeTax + $pensionEmployee + $otherDeductions;

        // Net salary
        $netSalary = $grossSalary - $totalDeductions;

        return [
            'employee_detail_id' => $employeeId,
            'basic_salary' => $basicSalary,
            'taxable_allowances' => $taxableAllowances,
            'non_taxable_allowances' => $nonTaxableAllowances,
            'overtime_regular_hours' => $regularHours,
            'overtime_sunday_hours' => $sundayHours,
            'overtime_holiday_hours' => $holidayHours,
            'overtime_pay' => $overtimePay,
            'gross_salary' => $grossSalary,
            'taxable_income' => $taxableIncome,
            'income_tax' => $incomeTax,
            'pension_employee' => $pensionEmployee,
            'pension_employer' => $pensionEmployer,
            'other_deductions' => $otherDeductions,
            'total_deductions' => $totalDeductions,
            'net_salary' => $netSalary,
        ];
    }
}
