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
        'working_days',
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
        'working_days' => 'decimal:2',
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
     * Calculate working days for an employee during a period
     * Based on actual attendance records
     * Sundays are counted as worked days (automatically added)
     * 
     * @param int $employeeId Employee Detail ID
     * @param string $periodStart Start date (Y-m-d)
     * @param string $periodEnd End date (Y-m-d)
     * @return float Working days
     */
    public static function calculateWorkingDays($employeeId, $periodStart, $periodEnd)
    {
        $employee = EmployeeDetail::with('user')->find($employeeId);
        if (!$employee || !$employee->user) {
            return 0;
        }

        $userId = $employee->user->id;
        $start = new \DateTime($periodStart);
        $end = new \DateTime($periodEnd);
        
        // Get all attendance timestamps for this user within the period
        $attendanceRecords = AttendanceTimestamp::where('user_id', $userId)
            ->whereBetween('created_at', [$periodStart . ' 00:00:00', $periodEnd . ' 23:59:59'])
            ->get();
        
        // Count unique dates when the employee attended
        $attendedDates = [];
        foreach ($attendanceRecords as $record) {
            $date = \Carbon\Carbon::parse($record->created_at)->format('Y-m-d');
            $attendedDates[$date] = true;
        }
        
        // Count Sundays in the period and add them as attended days
        $current = clone $start;
        while ($current <= $end) {
            $dayOfWeek = $current->format('w'); // 0 = Sunday, 6 = Saturday
            if ($dayOfWeek == 0) { // Sunday
                $dateStr = $current->format('Y-m-d');
                $attendedDates[$dateStr] = true;
            }
            $current->modify('+1 day');
        }
        
        // Subtract leave days (even if they attended, leave takes priority)
        $leaveRequests = LeaveRequest::where('employee_id', $userId)
            ->where('status', 'approved')
            ->where(function($query) use ($periodStart, $periodEnd) {
                $query->whereBetween('leave_start_date', [$periodStart, $periodEnd])
                      ->orWhereBetween('leave_end_date', [$periodStart, $periodEnd])
                      ->orWhere(function($q) use ($periodStart, $periodEnd) {
                          $q->where('leave_start_date', '<=', $periodStart)
                            ->where('leave_end_date', '>=', $periodEnd);
                      });
            })->get();
        
        $leaveDays = 0;
        foreach ($leaveRequests as $leave) {
            if ($leave->half_day) {
                $leaveDays += 0.5;
            } else {
                $leaveStart = max(new \DateTime($leave->leave_start_date), $start);
                $leaveEnd = min(new \DateTime($leave->leave_end_date), $end);
                $leaveInterval = $leaveStart->diff($leaveEnd);
                $leaveDays += $leaveInterval->days + 1;
            }
        }
        
        $workingDays = count($attendedDates) - $leaveDays;
        
        return max(0, $workingDays); // Ensure non-negative
    }

    /**
     * Calculate payroll for an employee
     */
    public static function calculatePayroll($employeeId, $overtimeData = [], $workingDays = null)
    {
        $employee = EmployeeDetail::with(['user', 'allowances', 'deductions', 'salaryDetails'])->find($employeeId);
        
        if (!$employee || !$employee->salaryDetails) {
            return null;
        }

        // Get basic salary
        $baseSalary = $employee->salaryDetails->base_salary ?? 0;

        // Prorate basic salary based on working days (out of 30 days expected)
        // Use provided working_days or fallback to default calculation
        if ($workingDays === null) {
            $workingDaysPerWeek = PayrollSetting::get('working_days_per_week', 5);
            $workingDays = $workingDaysPerWeek * 4; // Approximate monthly
        }
        
        $expectedWorkingDays = 30; // Standard monthly working days
        $basicSalary = ($baseSalary / $expectedWorkingDays) * $workingDays;

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

        // Calculate overtime using base salary and expected working days
        $workingHours = PayrollSetting::get('working_hours_per_day', 8);
        $hourlyRate = $baseSalary / ($expectedWorkingDays * $workingHours);

        $regularHours = $overtimeData['regular'] ?? 0;
        $sundayHours = $overtimeData['sunday'] ?? 0;
        $holidayHours = $overtimeData['holiday'] ?? 0;

        $regularRate = PayrollSetting::get('overtime_regular_rate', 1.5);
        $sundayRate = PayrollSetting::get('overtime_sunday_rate', 2.0);
        $holidayRate = PayrollSetting::get('overtime_holiday_rate', 2.5);

        $overtimePay = ($regularHours * $hourlyRate * $regularRate) +
                       ($sundayHours * $hourlyRate * $sundayRate) +
                       ($holidayHours * $hourlyRate * $holidayRate);

        // Calculate gross salary (using prorated basic salary)
        $grossSalary = $basicSalary + $totalAllowances + $overtimePay;

        // Calculate taxable income (using prorated basic salary)
        $taxableIncome = $basicSalary + $taxableAllowances + $overtimePay;

        // Calculate income tax
        $incomeTax = PayrollTaxBracket::calculateTax($taxableIncome);

        // Calculate pension (based on prorated basic salary)
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
            'working_days' => $workingDays,
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
