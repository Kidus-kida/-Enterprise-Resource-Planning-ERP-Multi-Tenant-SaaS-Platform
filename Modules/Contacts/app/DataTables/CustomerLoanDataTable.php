<?php

namespace Modules\Contacts\DataTables;

use Modules\Contacts\Models\Transaction;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

class CustomerLoanDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('transaction_date', function ($row) {
                return format_date($row->transaction_date);
            })
            ->addColumn('contact_name', function ($row) {
                return $row->contact ? $row->contact->name : '';
            })
            ->editColumn('final_total', function ($row) {
                return number_format($row->final_total, 2);
            })
            ->editColumn('is_settlement', function ($row) {
                return $row->is_settlement ? 'Yes' : 'No';
            })
            ->addColumn('action', function ($row) {
                return view('contacts::customer_loans.action', ['id' => $row->id]);
            })->rawColumns(['action']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Transaction $model): QueryBuilder
    {
        $query = $model->newQuery()
                    ->with('contact')
                    ->where('type', 'direct_customer_loan')
                    ->where('business_id', auth()->user()->business_id); // Filtering for loans
        
        if (request()->has('contact_id') && !empty(request()->contact_id)) {
            $query->where('contact_id', request()->contact_id);
        }
        
        if (request()->has('start_date') && !empty(request()->start_date)) {
            $query->whereDate('transaction_date', '>=', request()->start_date);
        }

        if (request()->has('end_date') && !empty(request()->end_date)) {
            $query->whereDate('transaction_date', '<=', request()->end_date);
        }

        return $query;
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('customer-loan-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    ->orderBy(0, 'desc')
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
            Column::make('ref_no')->title('Ref No'),
            Column::make('contact_name')->title('Customer')->name('contact.name'),
            Column::make('transaction_note')->title('Note'),
            Column::make('approved_user')->title('Approved By'),
            Column::make('final_total')->title('Amount'),
            Column::make('is_settlement')->title('Settlement?'),
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
        return 'CustomerLoan_' . date('YmdHis');
    }
}
