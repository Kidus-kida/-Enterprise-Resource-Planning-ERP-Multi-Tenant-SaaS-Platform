<?php

namespace App\DataTables;

use Modules\Contacts\Models\Transaction;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use DB;

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
            ->editColumn('transaction_date', function($row){
                return format_date($row->transaction_date);
            })
            ->editColumn('adjustment_type', function($row){
                return ucfirst($row->adjustment_type);
            })
             ->editColumn('stock_adjustment_type', function($row){
                return ucfirst($row->stock_adjustment_type);
            })
            ->addColumn('action', function ($row) {
                $id = $row->id;
                return view('stockadjustment::action', compact('id'));
            })
            ->rawColumns(['action']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Transaction $model): QueryBuilder
    {
        $business_id = auth()->user()->business_id;

        return $model->where('transactions.business_id', $business_id)
            ->where('transactions.type', 'stock_adjustment')
            ->leftJoin('business_locations as bl', 'transactions.location_id', '=', 'bl.id')
            ->leftJoin('users as u', 'transactions.created_by', '=', 'u.id')
            ->select(
                'transactions.id',
                'transaction_date',
                'ref_no',
                'bl.name as location_name',
                'adjustment_type',
                'stock_adjustment_type',
                'final_total',
                'total_amount_recovered',
                'additional_notes',
                DB::raw("CONCAT(COALESCE(u.firstname, ''),' ',COALESCE(u.middlename, ''),' ',COALESCE(u.lastname,'')) as added_by")
            );
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
            Column::make('transaction_date')->title('Date'),
            Column::make('ref_no')->title('Reference No'),
            Column::make('location_name')->title('Location'),
            Column::make('adjustment_type')->title('Adjustment Type'),
            Column::make('stock_adjustment_type')->title('Stock Adjustment Type'),
            Column::make('final_total')->title('Total Amount'),
            Column::make('total_amount_recovered')->title('Total Amount Recovered'),
            Column::make('additional_notes')->title('Reason'),
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
        return 'StockAdjustments_' . date('YmdHis');
    }
}
