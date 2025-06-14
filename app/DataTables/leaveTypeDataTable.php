<?php

namespace App\DataTables;
use App\Models\LeaveType;
use App\Models\Ticket;
use App\Enums\UserType;
use Spatie\Menu\Laravel\Html;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Illuminate\Support\Facades\Crypt;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

class LeaveTypeDataTable extends DataTable
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
            ->editColumn('type_name', fn($row) => $row->type_name ?? '')
            ->editColumn('max_date_allowed', fn($row) => $row->max_date_allowed ?? '')
            ->editColumn('leave_allowed_interval', fn($row) => $row->leave_allowed_interval ?? '')
            ->editColumn('description', fn($row) => $row->description ?? '')
            ->editColumn('status', fn($row) => ucfirst($row->status))
            ->editColumn('created_at', fn($row) => $row->created_at->format('Y-m-d H:i'))
            ->addColumn('action', function ($row) {
                $id = $row->id;
                return view('pages.tickets.action', compact('id'));
            });
    }
    /**
     * Get the query source of dataTable.
     */
    public function query()
    {
        if (auth()->user()->type === UserType::SUPERADMIN) {
            return LeaveType::query();
        }
        if (route_is('leavetypes')) {
            return LeaveType::where('status', 'allowed')
                ->newQuery();
        }
        return LeaveType::where('status', 'allowed')->newQuery();
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
            Column::make('type_name'),
            Column::make('max_date_allowed'),
            Column::make('leave_allowed_interval'),
            Column::make('description'),
            Column::make('status'),
            Column::make('created_at'),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(value: 60)
                // ->visible(auth()->user()->canAny(['edit-ticket', 'delete-ticket']))
                ->addClass('text-end'),
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
