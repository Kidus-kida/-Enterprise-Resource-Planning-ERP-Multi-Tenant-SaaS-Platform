<?php

namespace App\DataTables;
use App\Models\LeaveRequest;
use App\Enums\UserType;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Str;
use Illuminate\Database\Eloquent\Builder;

class myLeaveDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable($query)
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->editColumn('id', fn($row) => $row->id ?? '')
            ->editColumn('employee_id', fn($row) => $row->employee->name ?? 'N/A')
            ->editColumn('leave_type_id', fn($row) => $row->leaveType->type_name ?? 'N/A')
            ->editColumn('leave_start_date', fn($row) => $row->leave_start_date ?? '')
            ->editColumn('leave_end_date', fn($row) => $row->leave_end_date ?? '')
            // ->editColumn('request_reason', fn($row) => $row->request_reason ?? '')
            // In your dataTable() method:
            ->editColumn('request_reason', function ($row) {
                $full = $row->request_reason ?? '';
                $short = Str::limit($full, 30, '...');
                return '<span title="' . e($full) . '">' . e($short) . '</span>';
            })
            ->rawColumns(['request_reason']) // Add this to allow HTML rendering
            ->editColumn('created_at', fn($row) => $row->created_at->format('Y-m-d H:i'))
            ->editColumn('status', fn($row) => ucfirst($row->status))
        ;
    }
    /**
     * Get the query source of dataTable.
     */

    public function query2()
    {
        $query = LeaveRequest::with(['employee', 'leaveType']);

        if (auth()->user()->type === UserType::SUPERADMIN) {
            return $query;
        }

        return $query;
    }
    // adjust namespace if different

    public function query(): Builder
    {
        $query = LeaveRequest::with(['employee', 'leaveType']);
        // Everyone else: just their own leave‑requests
        return $query->where('employee_id', auth()->id());
    }





    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('leavetype-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(1)
            ->buttons([
                Button::make('excel'),
                Button::make('csv'),
                Button::make('pdf'),
                Button::make('print'),
                Button::make('reset'),
                Button::make('reload')
            ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::computed('id'),
            Column::make('employee_id')->title('Employee Name'),
            Column::make('leave_type_id')->title('Leave Type'),
            Column::make('leave_start_date'),
            Column::make('leave_end_date'),
            Column::make('request_reason'),
            Column::make('created_at'),
            Column::make('status'),

        ];
    }


    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Ticket_' . date('YmdHis');
    }
}
