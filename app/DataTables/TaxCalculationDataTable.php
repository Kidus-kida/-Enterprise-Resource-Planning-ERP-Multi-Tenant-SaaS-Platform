<?php

namespace App\DataTables;

use App\Models\TaxCalculation;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Button;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

class TaxCalculationDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('salary_range', function($row){
                if($row->salary_to) {
                    return $row->salary_from . ' - ' . $row->salary_to;
                }
                return $row->salary_from . '+';
            })
            ->editColumn('percentage', function($row){
                return $row->percentage . ' %';
            })
            ->editColumn('deducted_amount', function($row){
                return number_format($row->deducted_amount, 2) . ' Br';
            })
            ->addColumn('action', function($row){
                return view('pages.settings.tax management.actions', [
                    'id' => $row->id
                ]);
            })
            ->rawColumns(['action']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(TaxCalculation $model): QueryBuilder
    {
        return $model->newQuery();
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('taxcalculation-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    ->orderBy(0)
                    ->buttons([
                        Button::make('excel'),
                        Button::make('csv'),
                        Button::make('pdf'),
                        Button::make('print'),
                        Button::make('reload')
                    ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('id')->title('ID'),
            Column::computed('salary_range')->title('Salary Range'),
            Column::make('percentage')->title('Tax %'),
            Column::make('deducted_amount')->title('Deduction Amount'),
            Column::computed('action')
                  ->exportable(false)
                  ->printable(false)
                  ->width(100)
                  ->addClass('text-center'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'TaxCalculations_' . date('YmdHis');
    }
}
