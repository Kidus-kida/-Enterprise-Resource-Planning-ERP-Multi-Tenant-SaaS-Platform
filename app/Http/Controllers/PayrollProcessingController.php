<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Enums\UserType;
use App\Models\Department;
use App\Models\PayrollBatch;
use App\Models\PayrollDetail;
use App\Models\EmployeeDetail;
use Illuminate\Http\Request;

class PayrollProcessingController extends Controller
{
    /**
     * Display payroll batches
     */
    public function index()
    {
        $pageTitle = __('Payroll Processing');
        $batches = PayrollBatch::with('creator')->orderBy('created_at', 'desc')->paginate(20);
        
        return view('pages.payroll.processing.index', compact('pageTitle', 'batches'));
    }

    /**
     * Show employee selection form
     */
    public function create()
    {
        $pageTitle = __('Create Payroll');
        $employees = User::where('type', UserType::EMPLOYEE)
            ->whereHas('employeeDetail')
            ->where('is_active', true)
            ->with('employeeDetail')
            ->get();
        
        $departments = Department::all();
        
        return view('pages.payroll.processing.create', compact('pageTitle', 'employees', 'departments'));
    }

    /**
     * Process selected employees and show overtime form
     */
    public function selectEmployees(Request $request)
    {
        $request->validate([
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'pay_date' => 'required|date',
        ]);

        $employeeIds = [];

        // Get employees by selection method
        if ($request->selection_method == 'individual' && $request->has('employees')) {
            $employeeIds = $request->employees;
        } elseif ($request->selection_method == 'department' && $request->department_id) {
            $employeeIds = User::where('type', UserType::EMPLOYEE)
                ->where('is_active', true)
                ->where('department_id', $request->department_id)
                ->whereHas('employeeDetail')
                ->pluck('id')
                ->toArray();
        }

        if (empty($employeeIds)) {
            return back()->with(notify(__('Please select at least one employee'), 'error'));
        }

        $employees = User::whereIn('id', $employeeIds)
            ->with(['employeeDetail.salaryDetails', 'employeeDetail.allowances', 'employeeDetail.deductions'])
            ->get();

        $pageTitle = __('Enter Overtime Details');
        $periodStart = $request->period_start;
        $periodEnd = $request->period_end;
        $payDate = $request->pay_date;

        return view('pages.payroll.processing.overtime', compact(
            'pageTitle', 'employees', 'periodStart', 'periodEnd', 'payDate'
        ));
    }

    /**
     * Calculate and store payroll
     */
    public function store(Request $request)
    {
        $request->validate([
            'period_start' => 'required|date',
            'period_end' => 'required|date',
            'pay_date' => 'required|date',
            'employees' => 'required|array',
            'employees.*' => 'exists:employee_details,id',
        ]);

        // Create payroll batch
        $batch = PayrollBatch::create([
            'batch_number' => PayrollBatch::generateBatchNumber(),
            'period_start' => $request->period_start,
            'period_end' => $request->period_end,
            'pay_date' => $request->pay_date,
            'status' => 'draft',
            'created_by' => auth()->id(),
        ]);

        $totalGross = 0;
        $totalNet = 0;
        $totalEmployees = 0;

        // Process each employee
        foreach ($request->employees as $employeeId) {
            $overtimeData = [
                'regular' => $request->input("overtime_regular.{$employeeId}", 0),
                'sunday' => $request->input("overtime_sunday.{$employeeId}", 0),
                'holiday' => $request->input("overtime_holiday.{$employeeId}", 0),
            ];

            $payrollData = PayrollDetail::calculatePayroll($employeeId, $overtimeData);

            if ($payrollData) {
                $payrollData['payroll_batch_id'] = $batch->id;
                PayrollDetail::create($payrollData);

                $totalGross += $payrollData['gross_salary'];
                $totalNet += $payrollData['net_salary'];
                $totalEmployees++;
            }
        }

        // Update batch totals
        $batch->update([
            'total_employees' => $totalEmployees,
            'total_gross' => $totalGross,
            'total_net' => $totalNet,
        ]);

        $notification = notify(__('Payroll batch created successfully'));
        return redirect()->route('payroll.processing.show', $batch->id)->with($notification);
    }

    /**
     * Show payroll batch details
     */
    public function show($id)
    {
        $pageTitle = __('Payroll Batch Details');
        $batch = PayrollBatch::with(['details.employee.user', 'creator'])->findOrFail($id);

        return view('pages.payroll.processing.show', compact('pageTitle', 'batch'));
    }

    /**
     * Approve payroll batch
     */
    public function approve($id)
    {
        $batch = PayrollBatch::findOrFail($id);

        if ($batch->status !== 'draft') {
            return back()->with(notify(__('Only draft payrolls can be approved'), 'error'));
        }

        $batch->update(['status' => 'approved']);

        $notification = notify(__('Payroll batch approved successfully'));
        return back()->with($notification);
    }

    /**
     * Export payroll batch to Excel
     */
    public function export($id)
    {
        $batch = PayrollBatch::with(['details.employee.user', 'details.employee.salaryDetails'])->findOrFail($id);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set title
        $sheet->setCellValue('A1', 'TEWOS HR - Payroll Report');
        $sheet->mergeCells('A1:O1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Set batch info
        $sheet->setCellValue('A2', 'Batch: ' . $batch->batch_number);
        $sheet->setCellValue('E2', 'Period: ' . $batch->period_start->format('M d, Y') . ' - ' . $batch->period_end->format('M d, Y'));
        $sheet->setCellValue('K2', 'Pay Date: ' . $batch->pay_date->format('M d, Y'));

        // Set headers
        $row = 4;
        $headers = [
            'No', 'Employee Name', 'Account Number', 'Basic Salary', 'Taxable Allowances', 
            'Non-Taxable Allowances', 'Regular OT', 'Sunday OT', 'Holiday OT', 
            'Gross Salary', 'Income Tax', 'Pension (Employee)', 'Other Deductions', 
            'Total Deductions', 'Net Salary'
        ];
        
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $sheet->getStyle($col . $row)->getFont()->setBold(true);
            $sheet->getStyle($col . $row)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFE0E0E0');
            $col++;
        }

        // Add data
        $row = 5;
        $no = 1;
        foreach ($batch->details as $detail) {
            $sheet->setCellValue('A' . $row, $no++);
            $sheet->setCellValue('B' . $row, $detail->employee->user->fullname ?? 'N/A');
            $sheet->setCellValue('C' . $row, $detail->employee->salaryDetails->account_number ?? 'N/A');
            $sheet->setCellValue('D' . $row, $detail->basic_salary);
            $sheet->setCellValue('E' . $row, $detail->taxable_allowances);
            $sheet->setCellValue('F' . $row, $detail->non_taxable_allowances);
            $sheet->setCellValue('G' . $row, $detail->overtime_regular_hours . ' hrs');
            $sheet->setCellValue('H' . $row, $detail->overtime_sunday_hours . ' hrs');
            $sheet->setCellValue('I' . $row, $detail->overtime_holiday_hours . ' hrs');
            $sheet->setCellValue('J' . $row, $detail->gross_salary);
            $sheet->setCellValue('K' . $row, $detail->income_tax);
            $sheet->setCellValue('L' . $row, $detail->pension_employee);
            $sheet->setCellValue('M' . $row, $detail->other_deductions);
            $sheet->setCellValue('N' . $row, $detail->total_deductions);
            $sheet->setCellValue('O' . $row, $detail->net_salary);

            // Format currency columns
            foreach (['D', 'E', 'F', 'J', 'K', 'L', 'M', 'N', 'O'] as $currencyCol) {
                $sheet->getStyle($currencyCol . $row)->getNumberFormat()
                    ->setFormatCode('#,##0.00');
            }

            $row++;
        }

        // Add totals row
        $sheet->setCellValue('A' . $row, 'TOTAL');
        $sheet->mergeCells('A' . $row . ':C' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        
        $lastDataRow = $row - 1;
        $sheet->setCellValue('D' . $row, '=SUM(D5:D' . $lastDataRow . ')');
        $sheet->setCellValue('E' . $row, '=SUM(E5:E' . $lastDataRow . ')');
        $sheet->setCellValue('F' . $row, '=SUM(F5:F' . $lastDataRow . ')');
        $sheet->setCellValue('J' . $row, '=SUM(J5:J' . $lastDataRow . ')');
        $sheet->setCellValue('K' . $row, '=SUM(K5:K' . $lastDataRow . ')');
        $sheet->setCellValue('L' . $row, '=SUM(L5:L' . $lastDataRow . ')');
        $sheet->setCellValue('M' . $row, '=SUM(M5:M' . $lastDataRow . ')');
        $sheet->setCellValue('N' . $row, '=SUM(N5:N' . $lastDataRow . ')');
        $sheet->setCellValue('O' . $row, '=SUM(O5:O' . $lastDataRow . ')');
        
        $sheet->getStyle('A' . $row . ':O' . $row)->getFont()->setBold(true);
        foreach (['D', 'E', 'F', 'J', 'K', 'L', 'M', 'N', 'O'] as $currencyCol) {
            $sheet->getStyle($currencyCol . $row)->getNumberFormat()
                ->setFormatCode('#,##0.00');
        }

        // Auto-size columns
        foreach (range('A', 'O') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // Generate file
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $fileName = 'Payroll_' . $batch->batch_number . '_' . date('Ymd') . '.xlsx';
        
        $tempFile = tempnam(sys_get_temp_dir(), 'payroll');
        $writer->save($tempFile);

        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }
}
