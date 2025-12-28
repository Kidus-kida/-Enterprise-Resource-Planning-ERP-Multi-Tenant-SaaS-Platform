<?php

namespace App\DataTables;

use App\Models\AccountGroup;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

class StockAdjustmentDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->addColumn('type_name', function ($row) {
                return $row->accountType ? $row->accountType->name : 'N/A';
            })
            ->addColumn('action', function ($row) {
                $id = $row->id;
                return view('stockadjustment.action', compact('id'));
            })
            ->rawColumns(['action']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(AccountGroup $model): QueryBuilder
    {
        return $model->with('stockAdjustment:id,name')->newQuery();
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('stock-adjustments-table')
            ->columns($this->getColumns())
            ->parameters([
                'dom' => 'Bftip',
            ])
            ->minifiedAjax()
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
            Column::make('date')->title('Date'),
            Column::make('reference_no')->title('Reference No')->searchable(false)->orderable(false),
            Column::make('location')->title('Location'),
            Column::make('adjustment_type')->title('Adjustment Type'),
            Column::make('stock_adjustment_type')->title('Stock Adjustment Type'),
            Column::make('total_amount')->title('Total Amount'),
            Column::make('total_amount_recovered')->title('Total Amount Recovered'),
            Column::make('reason')->title('Reason'),
            Column::make('added_by')->title('Added By'),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->addClass('text-end'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'AccountGroups_' . date('YmdHis');
    }
}
