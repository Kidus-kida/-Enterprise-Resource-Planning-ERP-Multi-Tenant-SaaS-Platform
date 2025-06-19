<?php

namespace App\DataTables;
use App\Models\AnunalLeave;
use App\Models\LeaveRequest;
use App\Enums\UserType;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Str;

class AnnualLeaveDataTable extends DataTable
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
            ->editColumn('current_year', fn($row) => $row->current_year ?? 'N/A')
            ->editColumn('previous_year', fn($row) => $row->previous_year ?? '')
            // ->editColumn('year_bpy', fn($row) => $row->year_bpy ?? '')
            ->editColumn('per_month', fn($row) => $row->per_month ?? '')
            ->editColumn('per_year', fn($row) => $row->per_year ?? '')
            ->editColumn('total_anunal_leave', fn($row) => $row->total_anunal_leave ?? '')
            ->editColumn('created_at', fn($row) => $row->created_at->format('Y-m-d H:i'))
        ;
    }
    /**
     * Get the query source of dataTable.
     */

    public function query()
    {
        $query = AnunalLeave::with(['employee', 'leaveType']);

        if (auth()->user()->type === UserType::SUPERADMIN) {
            return $query;
        }

        return $query;
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
            Column::make('current_year')->title('This year'),
            Column::make('previous_year')->title('Previous year'),
            // Column::make('year_bpy'),
            Column::make('per_month'),
            Column::make('per_year'),
            Column::make('total_anunal_leave')->title('Total Leave'),
            Column::make('created_at'),

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
